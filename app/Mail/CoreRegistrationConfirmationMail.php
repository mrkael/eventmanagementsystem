<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoreRegistrationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ?Registration $registration,
        public string $ticketToken,
        public string $ticketQr,
        public string $renderedBody,
        public string $renderedSubject,
        public string $renderedHeader = '',
        public string $renderedFooter = '',
        public ?string $senderEmail = null,
        public ?string $senderName = null,
        public ?string $pdfData = null,
        public ?string $pdfFilename = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->senderEmail ? new Address($this->senderEmail, $this->senderName ?: config('mail.from.name')) : null,
            subject: $this->renderedSubject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.core.confirmation');
    }

    public function attachments(): array
    {
        if (! $this->pdfData) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $this->pdfData, $this->pdfFilename ?? 'eticket.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
