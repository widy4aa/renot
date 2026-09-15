<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificationCategory;
use App\Models\CertificationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * List semua kategori beserta jenis-jenisnya.
     */
    public function index(): JsonResponse
    {
        $kategori = CertificationCategory::with('types')->orderBy('name')->get();

        return response()->json(['data' => $kategori]);
    }

    /**
     * Tambah jenis sertifikasi baru ke kategori tertentu.
     */
    public function storeType(Request $request, CertificationCategory $kategori): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $type = $kategori->types()->create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Jenis sertifikasi berhasil ditambahkan.',
            'type' => $type,
        ], 201);
    }

    /**
     * Update jenis sertifikasi.
     */
    public function updateType(Request $request, CertificationCategory $kategori, CertificationType $type): JsonResponse
    {
        abort_if($type->category_id !== $kategori->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $type->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? $type->code,
        ]);

        return response()->json([
            'message' => 'Jenis sertifikasi berhasil diperbarui.',
            'type' => $type,
        ]);
    }

    /**
     * Hapus jenis sertifikasi.
     */
    public function destroyType(CertificationCategory $kategori, CertificationType $type): JsonResponse
    {
        abort_if($type->category_id !== $kategori->id, 404);

        // Cek apakah masih dipakai dokumen
        $used = $type->documents()->count();
        if ($used > 0) {
            return response()->json([
                'message' => "Jenis sertifikasi tidak bisa dihapus karena masih dipakai oleh {$used} dokumen.",
            ], 422);
        }

        $type->delete();

        return response()->json(['message' => 'Jenis sertifikasi berhasil dihapus.']);
    }
}
