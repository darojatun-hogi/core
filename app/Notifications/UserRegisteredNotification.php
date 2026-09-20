<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(protected User $registeredUser)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'New user registered',
            'message' => "{$this->registeredUser->username} just registered an account.",
            'actor_name' => $this->registeredUser->name,
            'url'     => route('users.show', $this->registeredUser),
        ];
    }
}