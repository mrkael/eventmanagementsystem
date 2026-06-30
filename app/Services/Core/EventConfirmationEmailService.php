<?php

namespace App\Services\Core;

use App\Mail\CoreRegistrationConfirmationMail;
use App\Models\EmailLog;
use App\Models\Event;
use App\Models\EventEmailTemplate;
use App\Models\Registration;
use App\Models\Ticket;
use App\Services\Attendance\QrCodeImageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;


class EventConfirmationEmailService
{
    public const TYPE = 'confirmation';

    public function __construct(
        private QrCodeImageService $qrCodeImageService,
        private ETicketPdfService $eTicketPdfService,
    ) {}

    public function templateFor(Event $event): EventEmailTemplate
    {
        return $event->confirmationEmailTemplate()->firstOrCreate(
            ['type' => self::TYPE],
            [
                'subject' => 'Registration Confirmation - {{event_name}}',
                'header_content' => 'Registration confirmed',
                'body_content' => $this->defaultBody(),
                'footer_content' => 'This is an automated confirmation email.',
                'is_active' => true,
            ],
        );
    }

    public function placeholderGroups(Event $event): array
    {
        $fieldPlaceholders = $event->registrationForms()
            ->with('fields')
            ->get()
            ->flatMap(fn ($form) => $form->fields)
            ->map(fn ($field) => '{{'.Str::slug($field->label, '_').'}}')
            ->unique()
            ->values()
            ->all();

        return [
            'Event' => ['{{event_name}}', '{{event_date}}', '{{event_location}}', '{{event_url}}'],
            'Participant' => ['{{participant_name}}', '{{participant_email}}', '{{registration_reference}}'],
            'Ticket' => ['{{ticket_name}}', '{{ticket_quantity}}'],
            'Registration Form Fields' => $fieldPlaceholders,
            'QR Code' => ['{{qr_code}}'],
        ];
    }

    public function saveTemplate(Event $event, array $data, int $userId): EventEmailTemplate
    {
        $template = $this->templateFor($event);
        $template->fill([
            'subject' => $data['subject'],
            'header_content' => $data['header_content'] ?? null,
            'body_content' => $data['body_content'],
            'footer_content' => $data['footer_content'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'updated_by' => $userId,
        ]);

        if (! $template->created_by) {
            $template->created_by = $userId;
        }

        $template->save();

        return $template;
    }

    public function preview(Event $event, ?EventEmailTemplate $template = null): array
    {
        $template ??= $this->templateFor($event);
        $ticket = $event->tickets()->with('form.fields')->where('status', 'active')->first();
        $sampleRegistration = new Registration([
            'event_id' => $event->id,
            'ticket_id' => $ticket?->id,
            'registration_form_id' => $ticket?->registration_form_id,
            'reference_number' => 'REG-SAMPLE-001',
            'full_name' => 'Sample Participant',
            'email' => 'participant@example.com',
            'status' => 'confirmed',
        ]);
        $sampleRegistration->setRelation('event', $event);
        if ($ticket) {
            $sampleRegistration->setRelation('ticket', $ticket);
        }

        return $this->render($event, $template, $sampleRegistration, $ticket, 1, $this->sampleAnswers($event), $this->qrCodeImageService->dataUri('SAMPLE-QR-CODE'));
    }

    public function sendTest(Event $event): EmailLog
    {
        $template = $this->templateFor($event);
        $rendered = $this->preview($event, $template);
        $recipient = config('mail.from.address');
        $sender = config('mail.from.address');

        return $this->sendRendered($event, null, 'confirmation_test', $recipient, null, $sender, $rendered, 'SAMPLE-QR-CODE');
    }

    public function sendRegistrationConfirmation(Registration $registration, string $rawToken, int $quantity = 1): void
    {
        $registration->loadMissing('event.organiserProfile', 'ticket.form.fields', 'answers');
        $event = $registration->event;
        $ticket = $registration->ticket;
        $template = $this->templateFor($event);
        $qrData = $this->qrCodeImageService->dataUri($rawToken);

        $registration->update([
            'qr_token' => $rawToken,
            'qr_code_data' => $qrData,
            'qr_generated_at' => now(),
        ]);

        if (! $template->is_active) {
            return;
        }

        $rendered = $this->render($event, $template, $registration, $ticket, $quantity, $this->answerValues($registration), $qrData);
        $recipient = $registration->email;
        $sender = config('mail.from.address');

        $pdfData = null;
        try {
            $pdfData = $this->eTicketPdfService->generate($registration, $rawToken);
        } catch (Throwable) {
        }

        $pdfFilename = 'eticket-' . Str::slug($registration->reference_number) . '.pdf';

        $log = $this->sendRendered($event, $registration, 'confirmation', $recipient, $registration->email, $sender, $rendered, $rawToken, $pdfData, $pdfFilename);

        if ($log->status === 'sent') {
            $registration->update(['confirmation_email_sent_at' => $log->sent_at]);
        }
    }

    public function render(Event $event, EventEmailTemplate $template, Registration $registration, ?Ticket $ticket, int $quantity, array $answers, string $qrData): array
    {
        $values = [
            '{{event_name}}' => $event->title,
            '{{event_date}}' => $event->starts_at?->format('d M Y, H:i') ?? '',
            '{{event_location}}' => $event->location ?? '',
            '{{event_url}}' => $event->custom_url ? url('/e/'.$event->custom_url) : '',
            '{{participant_name}}' => $registration->full_name,
            '{{participant_email}}' => $registration->email,
            '{{registration_reference}}' => $registration->reference_number,
            '{{ticket_name}}' => $ticket?->name ?? '',
            '{{ticket_quantity}}' => (string) $quantity,
            '{{qr_code}}' => '<img src="'.$qrData.'" alt="Registration QR code" width="180" height="180" style="display:block;max-width:180px;">',
        ];

        foreach ($answers as $key => $value) {
            $values['{{'.$key.'}}'] = is_array($value) ? implode(', ', $value) : (string) $value;
        }

        $contentValues = collect($values)
            ->mapWithKeys(fn ($value, $key) => [$key => $key === '{{qr_code}}' ? $value : e($value)])
            ->all();

        return [
            'subject' => strtr($template->subject, $values),
            'header' => $this->renderContent($template->header_content ?? '', $contentValues),
            'body' => $this->renderContent($template->body_content, $contentValues),
            'footer' => $this->renderContent($template->footer_content ?? '', $contentValues),
            'qr' => $qrData,
        ];
    }

    private function sendRendered(Event $event, ?Registration $registration, string $type, string $recipient, ?string $originalEmail, ?string $sender, array $rendered, string $token, ?string $pdfData = null, ?string $pdfFilename = null): EmailLog
    {
        $log = EmailLog::create([
            'event_id' => $event->id,
            'registration_id' => $registration?->id,
            'email_type' => $type,
            'recipient_email' => $recipient,
            'original_participant_email' => $originalEmail,
            'sender_email' => $sender,
            'subject' => $rendered['subject'],
            'status' => 'pending',
        ]);

        $lastException = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            if ($attempt > 0) {
                sleep(2);
            }

            try {
                Mail::to($recipient)->send(new CoreRegistrationConfirmationMail(
                    registration: $registration,
                    ticketToken: $token,
                    ticketQr: $rendered['qr'],
                    renderedBody: $rendered['body'],
                    renderedSubject: $rendered['subject'],
                    renderedHeader: $rendered['header'],
                    renderedFooter: $rendered['footer'],
                    senderEmail: $sender,
                    senderName: $event->organiserProfile?->name,
                    pdfData: $pdfData,
                    pdfFilename: $pdfFilename,
                ));

                $log->update(['status' => 'sent', 'sent_at' => now()]);
                Log::info('Confirmation email sent', [
                    'log_id' => $log->id,
                    'recipient' => $recipient,
                    'type' => $type,
                    'event_id' => $event->id,
                    'registration_id' => $registration?->id,
                ]);
                $lastException = null;
                break;
            } catch (Throwable $exception) {
                Log::warning('Confirmation email attempt failed', [
                    'log_id' => $log->id,
                    'attempt' => $attempt + 1,
                    'recipient' => $recipient,
                    'error' => $exception->getMessage(),
                ]);
                $lastException = $exception;
            }
        }

        if ($lastException) {
            $log->update([
                'status' => 'failed',
                'error_message' => Str::limit($lastException->getMessage(), 2000, ''),
            ]);
            Log::error('Confirmation email failed after all attempts', [
                'log_id' => $log->id,
                'recipient' => $recipient,
                'type' => $type,
                'event_id' => $event->id,
                'registration_id' => $registration?->id,
                'error' => $lastException->getMessage(),
            ]);
        }

        return $log->fresh();
    }

    private function renderContent(string $content, array $values): string
    {
        return nl2br(strtr(e($content), $values));
    }

    private function answerValues(Registration $registration): array
    {
        return $registration->answers->mapWithKeys(fn ($answer) => [
            Str::slug($answer->field_label ?: $answer->field_key, '_') => $answer->value,
        ])->all();
    }

    private function sampleAnswers(Event $event): array
    {
        return $event->registrationForms()
            ->with('fields')
            ->get()
            ->flatMap(fn ($form) => $form->fields)
            ->mapWithKeys(fn ($field) => [Str::slug($field->label, '_') => 'Sample '.$field->label])
            ->all();
    }

    private function defaultBody(): string
    {
        return "Hi {{participant_name}},\n\nThank you for registering for {{event_name}}.\n\nEvent: {{event_name}}\nDate: {{event_date}}\nLocation: {{event_location}}\nTicket: {{ticket_name}}\nRegistration Reference: {{registration_reference}}\n\nPlease present the QR code below during check-in:\n\n{{qr_code}}";
    }
}
