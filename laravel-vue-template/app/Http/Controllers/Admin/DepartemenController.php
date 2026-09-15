<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartemenController extends Controller
{
    public function index(): JsonResponse
    {
        $departemen = Department::withCount([
            'users as pegawai_count' => fn ($q) => $q->where('role', 'pegawai'),
            'users as pegawai_aktif_count' => fn ($q) => $q->where('role', 'pegawai')->where('is_active', true),
        ])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $departemen]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
        ]);

        $departemen = Department::create(['name' => $validated['name']]);

        return response()->json([
            'message' => 'Departemen berhasil ditambahkan.',
            'departemen' => $departemen->loadCount([
                'users as pegawai_count' => fn ($q) => $q->where('role', 'pegawai'),
                'users as pegawai_aktif_count' => fn ($q) => $q->where('role', 'pegawai')->where('is_active', true),
            ]),
        ], 201);
    }

    public function update(Request $request, Department $departemen): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:departments,name,'.$departemen->id],
        ]);

        $departemen->update(['name' => $validated['name']]);

        return response()->json([
            'message' => 'Departemen berhasil diperbarui.',
            'departemen' => $departemen->loadCount([
                'users as pegawai_count' => fn ($q) => $q->where('role', 'pegawai'),
                'users as pegawai_aktif_count' => fn ($q) => $q->where('role', 'pegawai')->where('is_active', true),
            ]),
        ]);
    }

    public function destroy(Department $departemen): JsonResponse
    {
        $aktivCount = $departemen->users()
            ->where('role', 'pegawai')
            ->where('is_active', true)
            ->count();

        if ($aktivCount > 0) {
            return response()->json([
                'message' => "Departemen tidak bisa dihapus karena masih memiliki {$aktivCount} pegawai aktif.",
            ], 422);
        }

        $departemen->delete();

        return response()->json(['message' => 'Departemen berhasil dihapus.']);
    }
}
