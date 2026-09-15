<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ManajemenAdminController extends Controller
{
    public function index(): JsonResponse
    {
        $admins = User::where('role', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'avatar', 'created_at'])
            ->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'email' => $a->email,
                'avatar' => $a->avatar ? Storage::url($a->avatar) : null,
                'created_at' => $a->created_at,
            ]);

        return response()->json(['data' => $admins]);
    }

    public function store(Request $request): JsonResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $newAdmin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        ActivityLog::record(
            activityType: 'admin_tambah',
            userId: $admin->id,
            subjectType: User::class,
            subjectId: $newAdmin->id,
            description: "Admin {$admin->name} menambahkan akun admin baru: {$newAdmin->name}.",
        );

        return response()->json([
            'message' => 'Akun admin berhasil dibuat.',
            'admin' => $newAdmin->only(['id', 'name', 'email', 'created_at']),
        ], 201);
    }

    public function update(Request $request, User $adminUser): JsonResponse
    {
        abort_if($adminUser->role !== 'admin', 404);

        $admin = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$adminUser->id],
        ]);

        $adminUser->update($validated);

        return response()->json([
            'message' => 'Data admin berhasil diperbarui.',
            'admin' => $adminUser->only(['id', 'name', 'email', 'created_at']),
        ]);
    }

    public function destroy(Request $request, User $adminUser): JsonResponse
    {
        abort_if($adminUser->role !== 'admin', 404);
        abort_if($adminUser->id === $request->user()->id, 422, 'Tidak bisa menghapus akun sendiri.');

        $admin = $request->user();

        ActivityLog::record(
            activityType: 'admin_hapus',
            userId: $admin->id,
            subjectType: User::class,
            subjectId: $adminUser->id,
            description: "Admin {$admin->name} menghapus akun admin: {$adminUser->name}.",
        );

        $adminUser->delete();

        return response()->json(['message' => 'Akun admin berhasil dihapus.']);
    }

    public function resetPassword(Request $request, User $adminUser): JsonResponse
    {
        abort_if($adminUser->role !== 'admin', 404);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $adminUser->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Password admin berhasil direset.']);
    }
}
