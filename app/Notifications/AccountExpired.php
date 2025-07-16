<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AccountExpired extends Notification implements ShouldQueue
{
    use Queueable;

    protected $expiredAt;

    public function __construct($expiredAt)
    {
        $this->expiredAt = $expiredAt;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your account has expired on ' . $this->expiredAt . '. Please contact the administrator to reactivate your account.',
            'type' => 'danger',
            'icon' => 'exclamation-triangle',
            'expired_at' => $this->expiredAt,
        ];
    }
} 