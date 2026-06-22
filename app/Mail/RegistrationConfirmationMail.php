<?php

namespace App\Mail;

use App\Models\ParticipantRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ParticipantRegistration $registration, public ?string $ticketToken = null, public ?string $ticketQr = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Registration confirmed: {$this->registration->event->title}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.registrations.confirmed');
    }
}
