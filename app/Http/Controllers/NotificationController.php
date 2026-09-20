<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        $notifications = $user->notifications()
            ->when($request->filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($request->filter === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function read(DatabaseNotification $notification)
    {
        abort_unless($notification->notifiable_id === Auth::id(), 403);

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        $data = $notification->data;

        if (isset($data['user_id']) && ! User::find($data['user_id'])) {
            return redirect()->route('users.index')
                ->with('info', 'The user in this notification no longer exists.');
        }

        return redirect($data['url']);
    }

    public function markAllRead()
    {
        /** @var User|null $user */
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
