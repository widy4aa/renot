<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = Document::where('user_id', $user->id)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(*) FILTER (WHERE status = 'aktif') as aktif,
                COUNT(*) FILTER (WHERE status = 'segera_expired') as segera_expired,
                COUNT(*) FILTER (WHERE status = 'expired') as expired,
                COUNT(*) FILTER (WHERE status = 'pending_approval') as pending_approval,
                COUNT(*) FILTER (WHERE status = 'ditolak') as ditolak
            ")
            ->first();

        $notifications = $user->notifications()
            ->where('channel', 'in_app')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'title', 'body', 'is_read', 'created_at', 'type']);

        return response()->json([
            'stats' => [
                'total' => (int) $stats->total,
                'aktif' => (int) $stats->aktif,
                'segera_expired' => (int) $stats->segera_expired,
                'expired' => (int) $stats->expired,
                'pending_approval' => (int) $stats->pending_approval,
                'ditolak' => (int) $stats->ditolak,
            ],
            'notifications' => $notifications,
        ]);
    }
}
