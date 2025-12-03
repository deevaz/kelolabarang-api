<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Password Request')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->line('Your reset token is: ' . $this->token)
            ->line('If you did not request a password reset, no further action is required.');
    }
}
