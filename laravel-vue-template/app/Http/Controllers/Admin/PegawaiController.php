<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', 'pegawai')
            ->with('department:id,name')
            ->withCount([
                'documents as documents_count',
                'documents as expired_count' => fn ($q) => $q->where('status', 'expired'),
                'documents as pending_count' => fn ($q) => $q->where('status', 'pending_approval'),
            ])
            ->orderBy('name');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$s}%")
                ->orWhere('email', 'ilike', "%{$s}%")
                ->orWhere('employee_number', 'ilike', "%{$s}%")
            );
        }
        if ($request->filled('departemen')) {
            $query->where('department_id', $request->departemen);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        return response()->json([
            'data' => $query->get()->map(fn ($u) => $this->formatUser($u)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'employee_number' => ['required', 'string', 'max:50', 'unique:users,employee_number'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_number' => $validated['employee_number'],
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
            'password' => Hash::make($validated['password']),
            'role' => 'pegawai',
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => $admin->id,
        ]);

        ActivityLog::record(
            activityType: 'pegawai_tambah',
            userId: $admin->id,
            subjectType: User::class,
            subjectId: $user->id,
            description: "Admin {$admin->name} menambahkan pegawai baru: {$user->name}.",
        );

        return response()->json([
            'message' => 'Akun pegawai berhasil dibuat.',
            'pegawai' => $this->formatUser($user->load('department:id,name')->loadCount([
                'documents as documents_count',
                'documents as expired_count' => fn ($q) => $q->where('status', 'expired'),
                'documents as pending_count' => fn ($q) => $q->where('status', 'pending_approval'),
            ])),
        ], 201);
    }

    public function show(User $pegawai): JsonResponse
    {
        abort_if($pegawai->role !== 'pegawai', 404);

        $pegawai->load('department:id,name')->loadCount([
            'documents as documents_count',
            'documents as expired_count' => fn ($q) => $q->where('status', 'expired'),
            'documents as pending_count' => fn ($q) => $q->where('status', 'pending_approval'),
        ]);

        return response()->json($this->formatUser($pegawai));
    }

    public function update(Request $request, User $pegawai): JsonResponse
    {
        abort_if($pegawai->role !== 'pegawai', 404);

        $admin = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$pegawai->id],
            'employee_number' => ['required', 'string', 'max:50', 'unique:users,employee_number,'.$pegawai->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'is_active' => ['boolean'],
        ]);

        $old = $pegawai->only(['name', 'email', 'employee_number', 'department_id', 'is_active']);
        $pegawai->update($validated);

        ActivityLog::record(
            activityType: 'pegawai_edit',
            userId: $admin->id,
            subjectType: User::class,
            subjectId: $pegawai->id,
            description: "Admin {$admin->name} mengedit data pegawai: {$pegawai->name}.",
            oldValues: $old,
            newValues: $pegawai->only(['name', 'email', 'employee_number', 'department_id', 'is_active']),
        );

        return response()->json([
            'message' => 'Data pegawai berhasil diperbarui.',
            'pegawai' => $this->formatUser($pegawai->load('department:id,name')->loadCount([
                'documents as documents_count',
                'documents as expired_count' => fn ($q) => $q->where('status', 'expired'),
                'documents as pending_count' => fn ($q) => $q->where('status', 'pending_approval'),
            ])),
        ]);
    }

    public function destroy(Request $request, User $pegawai): JsonResponse
    {
        abort_if($pegawai->role !== 'pegawai', 404);

        $admin = $request->user();

        // Hapus file dokumen pegawai dari storage
        foreach ($pegawai->documents as $doc) {
            if ($doc->file_path) {
                Storage::disk('public')->delete($doc->file_path);
            }
            foreach ($doc->versions as $v) {
                Storage::disk('public')->delete($v->file_path);
            }
        }

        // Hapus avatar jika ada
        if ($pegawai->avatar) {
            Storage::disk('public')->delete($pegawai->avatar);
        }

        ActivityLog::record(
            activityType: 'pegawai_hapus',
            userId: $admin->id,
            subjectType: User::class,
            subjectId: $pegawai->id,
            description: "Admin {$admin->name} menghapus pegawai: {$pegawai->name}.",
        );

        $pegawai->delete();

        return response()->json(['message' => 'Akun pegawai dan seluruh dokumennya berhasil dihapus.']);
    }

    public function toggleActive(Request $request, User $pegawai): JsonResponse
    {
        abort_if($pegawai->role !== 'pegawai', 404);

        $admin = $request->user();
        $pegawai->update(['is_active' => ! $pegawai->is_active]);

        $status = $pegawai->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLog::record(
            activityType: 'pegawai_toggle_active',
            userId: $admin->id,
            subjectType: User::class,
            subjectId: $pegawai->id,
            description: "Admin {$admin->name} {$status} akun pegawai: {$pegawai->name}.",
        );

        return response()->json([
            'message' => "Akun pegawai berhasil {$status}.",
            'is_active' => $pegawai->is_active,
        ]);
    }

    public function resetPassword(Request $request, User $pegawai): JsonResponse
    {
        abort_if($pegawai->role !== 'pegawai', 404);

        $admin = $request->user();

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $pegawai->update(['password' => Hash::make($validated['password'])]);

        ActivityLog::record(
            activityType: 'pegawai_reset_password',
            userId: $admin->id,
            subjectType: User::class,
            subjectId: $pegawai->id,
            description: "Admin {$admin->name} mereset password pegawai: {$pegawai->name}.",
        );

        return response()->json(['message' => 'Password pegawai berhasil direset.']);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_number' => $user->employee_number,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
            'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
            'created_at' => $user->created_at?->format('Y-m-d'),
            'department' => $user->relationLoaded('department') ? [
                'id' => $user->department?->id,
                'name' => $user->department?->name,
            ] : null,
            'documents_count' => $user->documents_count ?? 0,
            'expired_count' => $user->expired_count ?? 0,
            'pending_count' => $user->pending_count ?? 0,
        ];
    }
}
