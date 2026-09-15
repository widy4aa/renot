<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // ── Stats global ─────────────────────────────────────
        $totalPegawai = User::where('role', 'pegawai')
            ->where('is_active', true)
            ->count();

        $docStats = Document::selectRaw("
            COUNT(*) as total,
            COUNT(*) FILTER (WHERE status = 'aktif') as aktif,
            COUNT(*) FILTER (WHERE status = 'segera_expired') as segera_expired,
            COUNT(*) FILTER (WHERE status = 'expired') as expired,
            COUNT(*) FILTER (WHERE status = 'pending_approval') as pending_approval
        ")->first();

        // ── 5 dokumen pending terbaru ─────────────────────────
        $pendingDocuments = Document::with([
            'owner:id,name,avatar,department_id',
            'owner.department:id,name',
            'certificationType:id,name',
        ])
            ->where('status', Document::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'user_id', 'certification_type_id', 'status', 'created_at']);

        // ── 5 dokumen paling dekat expired ───────────────────
        $expiringSoon = Document::with([
            'owner:id,name',
            'certificationType:id,name',
        ])
            ->whereIn('status', [Document::STATUS_SEGERA_EXPIRED, Document::STATUS_EXPIRED])
            ->orderBy('expiry_date')
            ->limit(5)
            ->get(['id', 'user_id', 'certification_type_id', 'expiry_date', 'status']);

        // ── Top 5 departemen berdasarkan jumlah dokumen ───────
        $deptStats = Department::select('id', 'name')
            ->withCount([
                'users as pegawai_count' => fn ($q) => $q->where('role', 'pegawai')->where('is_active', true),
            ])
            ->get()
            ->map(function ($dep) {
                $baseQuery = fn () => Document::whereHas('owner', fn ($q) => $q->where('department_id', $dep->id));

                $counts = DB::table('documents')
                    ->join('users', 'documents.user_id', '=', 'users.id')
                    ->where('users.department_id', $dep->id)
                    ->selectRaw("
                        COUNT(*) as total,
                        COUNT(*) FILTER (WHERE documents.status = 'aktif') as aktif,
                        COUNT(*) FILTER (WHERE documents.status = 'segera_expired') as segera_expired,
                        COUNT(*) FILTER (WHERE documents.status = 'expired') as expired,
                        COUNT(*) FILTER (WHERE documents.status = 'pending_approval') as pending_approval
                    ")
                    ->first();

                return [
                    'name' => $dep->name,
                    'pegawai_count' => $dep->pegawai_count,
                    'total' => (int) $counts->total,
                    'aktif' => (int) $counts->aktif,
                    'segera_expired' => (int) $counts->segera_expired,
                    'expired' => (int) $counts->expired,
                    'pending' => (int) $counts->pending_approval,
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // ── 5 aktivitas terkini ────────────────────────────────
        $recentActivities = ActivityLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'activity_type', 'user_id', 'created_at'])
            ->map(fn ($log) => [
                'id' => $log->id,
                'activity_type' => $log->activity_type,
                'user_name' => $log->user?->name ?? 'System',
                'created_at' => $log->created_at?->toISOString(),
            ]);

        return response()->json([
            'stats' => [
                'total_pegawai' => $totalPegawai,
                'total_dokumen' => (int) $docStats->total,
                'pending_approval' => (int) $docStats->pending_approval,
                'expired' => (int) $docStats->expired,
                'segera_expired' => (int) $docStats->segera_expired,
                'aktif' => (int) $docStats->aktif,
            ],
            'pending_documents' => $pendingDocuments->map(fn ($d) => [
                'id' => $d->id,
                'user_name' => $d->owner?->name,
                'user_avatar' => $d->owner?->avatar ? Storage::url($d->owner->avatar) : null,
                'user_department' => $d->owner?->department?->name,
                'certification_type' => $d->certificationType?->name,
                'created_at' => $d->created_at?->toISOString(),
            ]),
            'expiring_soon' => $expiringSoon->map(fn ($d) => [
                'id' => $d->id,
                'user_name' => $d->owner?->name,
                'certification_type' => $d->certificationType?->name,
                'expiry_date' => $d->expiry_date?->toDateString(),
                'status' => $d->status,
            ]),
            'department_stats' => $deptStats,
            'recent_activities' => $recentActivities,
        ]);
    }
}
