<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApprovalController extends Controller
{
    /**
     * List semua dokumen dengan status pending_approval.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::with([
            'certificationType.category',
            'owner:id,name,avatar,employee_number,department_id',
            'owner.department:id,name',
        ])
            ->where('status', Document::STATUS_PENDING)
            ->orderByDesc('created_at');

        if ($request->filled('kategori')) {
            $query->whereHas('certificationType.category', fn ($q) => $q->where('name', $request->kategori));
        }
        if ($request->filled('departemen')) {
            $query->whereHas('owner', fn ($q) => $q->where('department_id', $request->departemen));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->whereHas('owner', fn ($q2) => $q2->where('name', 'ilike', "%{$s}%"))
                ->orWhereHas('certificationType', fn ($q2) => $q2->where('name', 'ilike', "%{$s}%"))
                ->orWhere('certificate_number', 'ilike', "%{$s}%")
            );
        }

        $docs = $query->get();

        return response()->json([
            'data' => $docs->map(fn ($d) => [
                'id' => $d->id,
                'certificate_number' => $d->certificate_number,
                'expiry_date' => $d->expiry_date?->toDateString(),
                'created_at' => $d->created_at?->toISOString(),
                'certification_type' => [
                    'id' => $d->certificationType->id,
                    'name' => $d->certificationType->name,
                    'category' => ['id' => $d->certificationType->category->id, 'name' => $d->certificationType->category->name],
                ],
                'user' => [
                    'id' => $d->owner->id,
                    'name' => $d->owner->name,
                    'avatar' => $d->owner->avatar ? Storage::url($d->owner->avatar) : null,
                    'employee_number' => $d->owner->employee_number,
                    'department' => $d->owner->department ? ['id' => $d->owner->department->id, 'name' => $d->owner->department->name] : null,
                ],
            ]),
            'total' => $docs->count(),
        ]);
    }
}
