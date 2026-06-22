<?php

namespace App\Mail;

use App\Models\ParticipantRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationCancellationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ParticipantRegistration $registration) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Registration cancelled: {$this->registration->event->title}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.registrations.cancelled');
    }
}
