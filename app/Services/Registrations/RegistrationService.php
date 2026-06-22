<?php

namespace App\Services\Registrations;

use App\Enums\ParticipantRegistrationStatus;
use App\Mail\RegistrationCancellationMail;
use App\Mail\RegistrationConfirmationMail;
use App\Mail\RegistrationWaitlistMail;
use App\Models\Event;
use App\Models\ParticipantRegistration;
use App\Models\RegistrationForm;
use App\Models\RegistrationInvite;
use App\Models\RegistrationQuestion;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\QrCodeImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public function __construct(private AttendanceService $attendanceService, private QrCodeImageService $qrCodeImageService) {}

    public function register(Event $event, array $data, string $source = 'public', ?User $user = null, ?RegistrationInvite $invite = null): ParticipantRegistration
    {
        $form = $event->registrationForm()->with('questions')->firstOrFail();
        $this->ensureFormAcceptsRegistration($event, $form, $source, $invite);
        if ($source !== 'bulk') {
            $this->validateAnswers($form, $data['answers'] ?? [], $data['answer_files'] ?? []);
        }

        return DB::transaction(function () use ($event, $form, $data, $source, $user, $invite) {
            $status = $this->initialStatus($event, $form);

            $registration = ParticipantRegistration::create([
                'event_id' => $event->id,
                'registration_form_id' => $form->id,
                'user_id' => $user?->id,
                'registration_invite_id' => $invite?->id,
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'phone' => $data['phone'] ?? null,
                'organization' => $data['organization'] ?? null,
                'status' => $status,
                'source' => $source,
                'approved_at' => $status === ParticipantRegistrationStatus::Confirmed ? now() : null,
            ]);

            $this->storeAnswers($registration, $form, $data['answers'] ?? [], $data['answer_files'] ?? []);

            if ($invite) {
                $invite->update(['status' => 'used', 'used_at' => now()]);
            }

            $this->sendStatusMail($registration->fresh('event'));

            return $registration->fresh(['event', 'answers.files']);
        });
    }

    public function changeStatus(ParticipantRegistration $registration, ParticipantRegistrationStatus $status): ParticipantRegistration
    {
        $registration->update([
            'status' => $status,
            'approved_at' => $status === ParticipantRegistrationStatus::Confirmed ? now() : $registration->approved_at,
            'cancelled_at' => $status === ParticipantRegistrationStatus::Cancelled ? now() : $registration->cancelled_at,
            'checked_in_at' => $status === ParticipantRegistrationStatus::Attended ? now() : $registration->checked_in_at,
        ]);

        if ($status === ParticipantRegistrationStatus::Confirmed || $status === ParticipantRegistrationStatus::Cancelled || $status === ParticipantRegistrationStatus::Waitlisted) {
            $this->sendStatusMail($registration->fresh('event'));
        }

        return $registration->fresh(['event', 'answers.files']);
    }

    public function createInvite(Event $event, User $user, array $data): RegistrationInvite
    {
        $form = $event->registrationForm()->firstOrFail();

        return RegistrationInvite::create([
            'event_id' => $event->id,
            'registration_form_id' => $form->id,
            'invited_by' => $user->id,
            'name' => $data['name'] ?? null,
            'email' => Str::lower($data['email']),
            'token' => Str::random(48),
            'expires_at' => $data['expires_at'] ?? now()->addDays(14),
        ]);
    }

    public function bulkUpload(Event $event, UploadedFile $file, User $user): int
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = $handle ? fgetcsv($handle) : false;
        $created = 0;

        if (! $handle || ! $header) {
            throw ValidationException::withMessages(['file' => 'The CSV file must include a header row.']);
        }

        $header = array_map(fn ($column) => Str::slug((string) $column, '_'), $header);

        while (($row = fgetcsv($handle)) !== false) {
            $record = array_combine($header, $row);

            if (! $record || blank($record['email'] ?? null) || blank($record['name'] ?? null)) {
                continue;
            }

            $this->register($event, [
                'name' => $record['name'],
                'email' => $record['email'],
                'phone' => $record['phone'] ?? null,
                'organization' => $record['organization'] ?? null,
                'answers' => [],
                'answer_files' => [],
            ], 'bulk', $user);
            $created++;
        }

        fclose($handle);

        return $created;
    }

    private function ensureFormAcceptsRegistration(Event $event, RegistrationForm $form, string $source, ?RegistrationInvite $invite): void
    {
        if (! $form->is_enabled || ! $event->is_registration_enabled) {
            throw ValidationException::withMessages(['registration' => 'Registration is not open for this event.']);
        }

        if ($form->opens_at && now()->lt($form->opens_at)) {
            throw ValidationException::withMessages(['registration' => 'Registration has not opened yet.']);
        }

        if ($form->closes_at && now()->gt($form->closes_at)) {
            throw ValidationException::withMessages(['registration' => 'Registration has closed.']);
        }

        if ($source === 'public' && $form->access_mode !== 'public') {
            throw ValidationException::withMessages(['registration' => 'This event requires a private or invite registration link.']);
        }

        if ($source === 'invite' && (! $invite || $invite->status !== 'pending' || ($invite->expires_at && now()->gt($invite->expires_at)))) {
            throw ValidationException::withMessages(['registration' => 'This invite link is invalid or expired.']);
        }
    }

    private function validateAnswers(RegistrationForm $form, array $answers, array $files): void
    {
        $rules = [];
        $messages = [];

        foreach ($form->questions as $question) {
            $key = "answers.{$question->key}";
            $base = $question->type === 'file'
                ? ['nullable']
                : ($question->is_required ? ['required'] : ['nullable']);

            $rules[$key] = array_merge($base, $this->rulesFor($question));
            $messages["{$key}.required"] = "{$question->label} is required.";

            if ($question->type === 'file') {
                $rules["answer_files.{$question->key}"] = array_merge($question->is_required ? ['required'] : ['nullable'], ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx']);
            }
        }

        Validator::make(['answers' => $answers, 'answer_files' => $files], $rules, $messages)->validate();
    }

    private function rulesFor(RegistrationQuestion $question): array
    {
        $rules = match ($question->type) {
            'email' => ['email:rfc', 'max:255'],
            'number' => ['numeric'],
            'date' => ['date'],
            'dropdown', 'radio' => ['string', 'max:255'],
            'checkbox' => ['array'],
            'textarea' => ['string', 'max:5000'],
            'file' => ['nullable'],
            default => ['string', 'max:255'],
        };

        return array_merge($rules, $question->validation_rules ?? []);
    }

    private function initialStatus(Event $event, RegistrationForm $form): ParticipantRegistrationStatus
    {
        $confirmedCount = $event->participantRegistrations()
            ->whereIn('status', [ParticipantRegistrationStatus::Confirmed->value, ParticipantRegistrationStatus::Attended->value])
            ->count();

        if ($event->capacity > 0 && $confirmedCount >= $event->capacity && $form->allow_waitlist) {
            return ParticipantRegistrationStatus::Waitlisted;
        }

        return $form->requires_approval
            ? ParticipantRegistrationStatus::Pending
            : ParticipantRegistrationStatus::Confirmed;
    }

    private function storeAnswers(ParticipantRegistration $registration, RegistrationForm $form, array $answers, array $files): void
    {
        foreach ($form->questions as $question) {
            $answer = $registration->answers()->create([
                'registration_question_id' => $question->id,
                'question_key' => $question->key,
                'question_label' => $question->label,
                'question_type' => $question->type,
                'value' => $question->type === 'file' ? null : ($answers[$question->key] ?? null),
            ]);

            $file = $files[$question->key] ?? null;
            if ($question->type === 'file' && $file instanceof UploadedFile) {
                $answer->files()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $file->store("registrations/{$registration->id}", 'public'),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize() ?: 0,
                ]);
            }
        }
    }

    private function sendStatusMail(ParticipantRegistration $registration): void
    {
        match ($registration->status) {
            ParticipantRegistrationStatus::Confirmed => $this->sendConfirmationWithTicket($registration),
            ParticipantRegistrationStatus::Waitlisted => Mail::to($registration->email)->send(new RegistrationWaitlistMail($registration)),
            ParticipantRegistrationStatus::Cancelled => Mail::to($registration->email)->send(new RegistrationCancellationMail($registration)),
            default => null,
        };
    }

    private function sendConfirmationWithTicket(ParticipantRegistration $registration): void
    {
        $ticket = $this->attendanceService->generateQr($registration);
        Mail::to($registration->email)->send(new RegistrationConfirmationMail(
            registration: $registration,
            ticketToken: $ticket['token'],
            ticketQr: $this->qrCodeImageService->dataUri($ticket['token']),
        ));
    }
}
