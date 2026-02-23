<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;

class ClientNotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $notifications = $user->notifications()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('client.notifications', compact('notifications', 'unreadCount'));
    }

    public function markRead(UserNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        if (!$notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }

        // if it's a dropdown click, redirect to url if exists
        return back();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }
}