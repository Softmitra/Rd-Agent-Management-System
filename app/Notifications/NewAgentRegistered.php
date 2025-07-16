<?php

namespace App\Notifications;

use App\Models\Agent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewAgentRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    protected $agent;

    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "New agent {$this->agent->name} has registered and is pending verification.",
            'agent_id' => $this->agent->id,
            'agent_name' => $this->agent->name,
            'agent_email' => $this->agent->email,
            'created_at' => now(),
        ];
    }
} 