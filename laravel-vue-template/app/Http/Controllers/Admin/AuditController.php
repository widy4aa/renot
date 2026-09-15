<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user:id,name,role')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('description', 'ilike', "%{$s}%")
                ->orWhereHas('user', fn ($q2) => $q2->where('name', 'ilike', "%{$s}%"))
            );
        }
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => collect($paginated->items())->map(fn ($log) => [
                'id' => $log->id,
                'activity_type' => $log->activity_type,
                'description' => $log->description,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toISOString(),
                'user' => $log->user ? ['id' => $log->user->id, 'name' => $log->user->name, 'role' => $log->user->role] : null,
            ]),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function activityTypes(): JsonResponse
    {
        $types = ActivityLog::select('activity_type')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type');

        return response()->json(['data' => $types]);
    }
}
