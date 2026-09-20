<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected User $targetUser,
        protected User $actor,
        protected string $action // 'created' | 'updated'
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'User ' . $this->action,
            'message' => "{$this->actor->name} {$this->action} user {$this->targetUser->name}.",
            'user_id' => $this->targetUser->id,
            'actor_name'=> $this->actor->name,
            'url'     => $this->action === 'deleted'
                            ? route('users.index')
                            : route('users.show', $this->targetUser),
        ];
    }
}