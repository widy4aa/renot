<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->where('channel', 'in_app')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'body', 'type', 'is_read', 'read_at', 'document_id', 'created_at']);

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['message' => 'OK']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->notifications()
            ->where('channel', 'in_app')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'OK']);
    }
}
