<?php

namespace App\Services\Core;

use App\Mail\CoreRegistrationConfirmationMail;
use App\Models\EmailTemplate;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\Registration;
use App\Models\RegistrationForm;
use App\Models\RegistrationFormField;
use App\Models\Ticket;
use App\Services\Attendance\QrCodeImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CoreRegistrationService
{
    public function __construct(private QrCodeImageService $qrCodeImageService) {}

    public function register(Event $event, Ticket $ticket, array $data): Registration
    {
        $form = $ticket->form()->with('fields')->first();
        if (! $form) {
            throw ValidationException::withMessages(['ticket' => 'This ticket does not have a registration form.']);
        }

        $this->validateAvailability($event, $ticket, $data['email'] ?? null);
        $this->validateDynamicFields($form, $data['answers'] ?? [], $data['answer_files'] ?? []);

        return DB::transaction(function () use ($event, $ticket, $form, $data) {
            $ticket = Ticket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            $this->validateAvailability($event, $ticket, $data['email'] ?? null);

            $pricing = $this->pricing($event, $ticket, $data['promo_code'] ?? null);
            $rawToken = 'CORE-'.$event->id.'-'.Str::upper(Str::random(36));

            $registration = Registration::create([
                'event_id' => $event->id,
                'ticket_id' => $ticket->id,
                'registration_form_id' => $form->id,
                'reference_number' => 'REG-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'full_name' => $data['full_name'],
                'email' => Str::lower($data['email']),
                'phone' => $data['phone'] ?? null,
                'organization' => $data['organization'] ?? null,
                'designation' => $data['designation'] ?? null,
                'status' => 'confirmed',
                'payment_status' => $pricing['final'] > 0 ? 'pending' : 'not_applicable',
                'ticket_price' => $pricing['price'],
                'discount_amount' => $pricing['discount'],
                'final_amount' => $pricing['final'],
                'promo_code' => $pricing['promo']?->code,
                'qr_token_hash' => hash('sha256', $rawToken),
            ]);

            $ticket->decrement('available_quantity');
            if ($pricing['promo']) {
                $pricing['promo']->increment('used_count');
            }

            $this->storeAnswers($registration, $form, $data['answers'] ?? [], $data['answer_files'] ?? []);
            $this->sendConfirmation($registration->fresh(['event', 'ticket']), $rawToken);

            return $registration->fresh(['event', 'ticket', 'answers']);
        });
    }

    public function resendConfirmation(Registration $registration): void
    {
        $rawToken = 'CORE-'.$registration->event_id.'-'.Str::upper(Str::random(36));
        $registration->update(['qr_token_hash' => hash('sha256', $rawToken)]);
        $this->sendConfirmation($registration->fresh(['event', 'ticket']), $rawToken);
    }

    private function validateAvailability(Event $event, Ticket $ticket, ?string $email = null): void
    {
        $eventStatus = $event->status_key instanceof \BackedEnum ? $event->status_key->value : (string) $event->status_key;

        if ($eventStatus !== 'published') {
            throw ValidationException::withMessages(['event' => 'This event is not open for registration.']);
        }

        if ($event->registration_opens_at && now()->lt($event->registration_opens_at)) {
            throw ValidationException::withMessages(['event' => 'Registration has not opened yet.']);
        }

        if ($event->registration_closes_at && now()->gt($event->registration_closes_at)) {
            throw ValidationException::withMessages(['event' => 'Registration has closed.']);
        }

        if ($ticket->status !== 'active' || $ticket->available_quantity < 1) {
            throw ValidationException::withMessages(['ticket' => 'This ticket is no longer available.']);
        }

        if ($ticket->sales_start_at && now()->lt($ticket->sales_start_at)) {
            throw ValidationException::withMessages(['ticket' => 'Ticket sales have not started.']);
        }

        if ($ticket->sales_end_at && now()->gt($ticket->sales_end_at)) {
            throw ValidationException::withMessages(['ticket' => 'Ticket sales have ended.']);
        }

        if ($event->capacity > 0 && $event->coreRegistrations()->where('status', 'confirmed')->count() >= $event->capacity) {
            throw ValidationException::withMessages(['event' => 'Event capacity is full.']);
        }

        if (! $event->allow_duplicate_email && $email) {
            $exists = $event->coreRegistrations()
                ->where('email', Str::lower($email))
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();
            if ($exists) {
                throw ValidationException::withMessages(['email' => 'This email has already registered for this event.']);
            }
        }
    }

    private function validateDynamicFields(RegistrationForm $form, array $answers, array $files): void
    {
        $rules = [];
        foreach ($form->fields as $field) {
            $key = "answers.{$field->key}";
            $rules[$key] = array_merge($field->is_required && $field->type !== 'file' ? ['required'] : ['nullable'], $this->rulesFor($field));
            if ($field->type === 'file') {
                $rules["answer_files.{$field->key}"] = array_merge($field->is_required ? ['required'] : ['nullable'], ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx']);
            }
        }

        Validator::make(['answers' => $answers, 'answer_files' => $files], $rules)->validate();
    }

    private function rulesFor(RegistrationFormField $field): array
    {
        return match ($field->type) {
            'email' => ['email:rfc', 'max:255'],
            'number' => ['numeric'],
            'date' => ['date'],
            'checkbox' => ['array'],
            'textarea' => ['string', 'max:5000'],
            'file' => ['nullable'],
            default => ['string', 'max:255'],
        };
    }

    private function pricing(Event $event, Ticket $ticket, ?string $promoCode): array
    {
        $price = $ticket->early_bird_price !== null && $ticket->sales_start_at && now()->lte($ticket->sales_start_at->copy()->addDays(7))
            ? (float) $ticket->early_bird_price
            : (float) $ticket->price;
        $promo = null;
        $discount = 0;

        if ($promoCode) {
            $promo = PromoCode::where('event_id', $event->id)->where('code', Str::upper($promoCode))->where('is_active', true)->first();
            if ($promo && (!$promo->ticket_id || $promo->ticket_id === $ticket->id)
                && (!$promo->valid_from || now()->gte($promo->valid_from))
                && (!$promo->valid_until || now()->lte($promo->valid_until))
                && (!$promo->usage_limit || $promo->used_count < $promo->usage_limit)) {
                $discount = $promo->discount_type === 'percentage' ? ($price * ((float) $promo->discount_value / 100)) : (float) $promo->discount_value;
            } else {
                $promo = null;
            }
        }

        $subtotal = max(0, $price - $discount);
        $tax = $event->payment_tax_percentage > 0 ? ($subtotal * ((float) $event->payment_tax_percentage / 100)) : 0;
        $final = $subtotal + $tax;

        return compact('price', 'discount', 'final', 'promo');
    }

    private function storeAnswers(Registration $registration, RegistrationForm $form, array $answers, array $files): void
    {
        foreach ($form->fields as $field) {
            $filePath = null;
            if ($field->type === 'file' && ($files[$field->key] ?? null) instanceof UploadedFile) {
                $filePath = $files[$field->key]->store("core-registrations/{$registration->id}", 'public');
            }

            $registration->answers()->create([
                'registration_form_field_id' => $field->id,
                'field_key' => $field->key,
                'field_label' => $field->label,
                'field_type' => $field->type,
                'value' => $field->type === 'file' ? null : ($answers[$field->key] ?? null),
                'file_path' => $filePath,
            ]);
        }
    }

    private function sendConfirmation(Registration $registration, string $rawToken): void
    {
        $template = EmailTemplate::where('event_id', $registration->event_id)->where('type', 'confirmation')->where('is_active', true)->first()
            ?? EmailTemplate::whereNull('event_id')->where('type', 'confirmation')->where('is_active', true)->first();

        $subject = $template?->subject ?? 'Registration confirmed: {{ event_name }}';
        $body = $template?->body ?? "Hello {{ participant_name }},\n\nYour registration for {{ event_name }} is confirmed.\nTicket: {{ ticket_name }}\nReference: {{ registration_reference }}\n\n{{ qr_code }}";
        $values = [
            '{{ participant_name }}' => $registration->full_name,
            '{{ event_name }}' => $registration->event->title,
            '{{ event_date }}' => $registration->event->starts_at->format('d M Y, H:i'),
            '{{ event_location }}' => $registration->event->location ?? '',
            '{{ ticket_name }}' => $registration->ticket->name,
            '{{ registration_reference }}' => $registration->reference_number,
            '{{ qr_code }}' => '',
        ];

        Mail::to($registration->email)->send(new CoreRegistrationConfirmationMail(
            registration: $registration,
            ticketToken: $rawToken,
            ticketQr: $this->qrCodeImageService->dataUri($rawToken),
            renderedBody: nl2br(e(strtr($body, $values))),
            renderedSubject: strtr($subject, $values),
        ));
    }
}
