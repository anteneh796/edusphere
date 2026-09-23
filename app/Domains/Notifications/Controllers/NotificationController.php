<?php

namespace App\Domains\Notifications\Controllers;

use App\Domains\Notifications\Models\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Notification::class);

        $notifications = Notification::where('user_id', auth()->id())
            ->when($request->query('filter') === 'unread', fn ($query) => $query->unread())
            ->when($request->query('filter') === 'read', fn ($query) => $query->read())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $unreadCount = Notification::where('user_id', auth()->id())->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function show(Notification $notification): RedirectResponse
    {
        $this->authorize('update', $notification);

        $notification->markRead();

        $url = $notification->redirect_url ?? route('notifications.index');

        return redirect()->to($url);
    }

    public function readAll(): RedirectResponse
    {
        $this->authorize('viewAny', Notification::class);

        Notification::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        return redirect()
            ->route('notifications.index')
            ->with('status', 'All notifications marked as read.');
    }
}
