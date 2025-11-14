<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status');

        $query = AdminNotification::query()->latest();

        if ($status === 'unread') {
            $query->unread();
        } elseif ($status === 'read') {
            $query->where('is_read', true);
        }

        /** @var LengthAwarePaginator $notifications */
        $notifications = $query->paginate(20)->withQueryString();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'status' => $status,
        ]);
    }

    public function markAsRead(AdminNotification $notification): RedirectResponse
    {
        $notification->markAsRead();

        return back()->with('status', __('admin.notifications_marked_read'));
    }

    public function markAllAsRead(): RedirectResponse
    {
        $now = now();

        AdminNotification::unread()->update([
            'is_read' => true,
            'read_at' => $now,
            'updated_at' => $now,
        ]);

        return back()->with('status', __('admin.notifications_all_marked_read'));
    }
}
