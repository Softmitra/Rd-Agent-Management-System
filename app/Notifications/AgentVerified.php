<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgentVerified extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $verifierName,
        protected ?string $remarks = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your Agent Account Has Been Verified')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your agent account has been verified by ' . $this->verifierName . '.')
            ->line('You can now log in to your account and start managing your RD accounts.');

        if ($this->remarks) {
            $message->line('Verification Remarks: ' . $this->remarks);
        }

        $message->action('Login to Your Account', route('login'))
            ->line('Thank you for using our RD Agent System!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'verifier_name' => $this->verifierName,
            'remarks' => $this->remarks,
        ];
    }
}
