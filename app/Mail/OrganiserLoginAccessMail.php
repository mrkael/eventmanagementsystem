<?php

namespace App\Mail;

use App\Models\OrganiserProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganiserLoginAccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OrganiserProfile $organiser,
        public User $user,
        public ?string $temporaryPassword = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').' organiser login access',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.core.organiser-login-access',
        );
    }
}
