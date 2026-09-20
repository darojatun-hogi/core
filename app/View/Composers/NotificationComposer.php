<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class NotificationComposer
{
    public function compose(View $view): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        $view->with('navbarNotifications', $user
            ? $user->notifications()->latest()->take(5)->get()
            : collect());

        $view->with('unreadNotificationsCount', $user
            ? $user->unreadNotifications()->count()
            : 0);
    }
}