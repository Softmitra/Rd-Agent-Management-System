<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AccountExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    protected $daysRemaining;
    protected $expiresAt;

    public function __construct($daysRemaining, $expiresAt)
    {
        $this->daysRemaining = $daysRemaining;
        $this->expiresAt = $expiresAt;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Your account will expire in {$this->daysRemaining} days on {$this->expiresAt}. Please contact the administrator to extend your account.",
            'type' => 'warning',
            'icon' => 'clock',
            'days_remaining' => $this->daysRemaining,
            'expires_at' => $this->expiresAt,
        ];
    }
} 