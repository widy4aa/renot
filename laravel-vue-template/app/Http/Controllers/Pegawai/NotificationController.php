<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Daftar semua notifikasi in-app milik pegawai,
     * dikelompokkan: unread dulu, lalu read — masing-masing terbaru di atas.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->where('channel', 'in_app')
            ->orderByRaw('is_read ASC, created_at DESC')
            ->get()
            ->map(fn ($n) => $this->formatNotification($n));

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('channel', 'in_app')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'notification' => $this->formatNotification($notification),
        ]);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = Notification::where('user_id', $request->user()->id)
            ->where('channel', 'in_app')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
            'updated' => $updated,
        ]);
    }

    private function formatNotification(Notification $n): array
    {
        return [
            'id' => $n->id,
            'type' => $n->type,
            'title' => $n->title,
            'body' => $n->body,
            'is_read' => $n->is_read,
            'read_at' => $n->read_at?->format('Y-m-d H:i'),
            'created_at' => $n->created_at?->toIso8601String(),
            'document_id' => $n->document_id,
        ];
    }
}
