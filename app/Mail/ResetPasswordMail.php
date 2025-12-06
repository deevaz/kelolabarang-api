<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $code; 
    public $email;
    public $expiresAt;
    public $expiryMinutes;

    public function __construct($name, $code, $email, $expiresAt)
    {
        $this->name = $name;
        $this->code = $code;
        $this->email = $email;
        $this->expiresAt = $expiresAt;
        $this->expiryMinutes = 10; 
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@abdaziz.my.id', config('app.name')),
            subject: 'Kode Reset Password - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.reset-password',
            with: [
                'name' => $this->name,
                'code' => $this->code,
                'email' => $this->email,
                'expiryMinutes' => $this->expiryMinutes,
                'expiresAt' => $this->expiresAt->format('H:i'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}