<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user('admin');
        if (!$admin) {
            abort(403);
        }

        $notifications = $admin->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($n) {
                $data = $n->data ?? [];
                return [
                    'id'          => $n->id,
                    'title'       => $data['title'] ?? 'New order',
                    'body'        => $data['body'] ?? '',
                    'created_at'  => $n->created_at?->diffForHumans(),
                    'read_at'     => $n->read_at,
                    'order_id'    => $data['order_id'] ?? null,
                    'package'     => $data['package'] ?? null,
                    'client'      => $data['client'] ?? null,
                    'type'        => $data['type'] ?? null,
                    'payment'     => $data['payment'] ?? null,
                    'price'       => $data['price'] ?? null,
                    'phone'       => $data['phone'] ?? null,
                ];
            });

        return response()->json([
            'unread_count' => $admin->unreadNotifications()->count(),
            'items'        => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $admin = $request->user('admin');
        if (!$admin) abort(403);

        $notification = $admin->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $admin = $request->user('admin');
        if (!$admin) abort(403);

        $admin->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
