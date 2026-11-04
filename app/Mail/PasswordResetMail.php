<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetMail extends Mailable
{
    public function __construct(
        public string $url,
        public string $name,
        public int $minutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'LIMS3.0 Password Reset');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-reset');
    }
}