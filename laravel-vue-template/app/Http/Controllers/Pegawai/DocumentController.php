<?php

namespace App\Http\Controllers\Pegawai;

use App\Exports\MyDocumentsExport;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DocumentController extends Controller
{
    /**
     * Daftar dokumen milik pegawai (terbaru per jenis sertifikasi).
     * Filter by status via query param.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Document::where('user_id', $user->id)
            ->with(['certificationType.category'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ambil semua, lalu filter terbaru per jenis di PHP
        $all = $query->get();

        if (! $request->filled('status')) {
            // Tampilkan hanya terbaru per certification_type_id
            $all = $all->unique('certification_type_id')->values();
        }

        return response()->json([
            'data' => $all->map(fn ($doc) => $this->formatDocument($doc)),
        ]);
    }

    /**
     * Detail satu dokumen.
     */
    public function show(Request $request, Document $document): JsonResponse
    {
        if ($document->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $document->load(['certificationType.category', 'approver', 'versions']);

        return response()->json($this->formatDocument($document, detailed: true));
    }

    /**
     * Upload dokumen baru.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'certification_type_id' => ['required', 'exists:certification_types,id'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'implementation_date' => ['nullable', 'date'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['required', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $filePath = null;
        $fileName = null;
        $fileSize = null;
        $fileMime = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('documents', 'public');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileMime = $file->getMimeType();
        }

        $document = Document::create([
            'user_id' => $user->id,
            'certification_type_id' => $validated['certification_type_id'],
            'certificate_number' => $validated['certificate_number'] ?? null,
            'implementation_date' => $validated['implementation_date'] ?? null,
            'issued_date' => $validated['issued_date'] ?? null,
            'expiry_date' => $validated['expiry_date'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'file_mime' => $fileMime,
            'status' => Document::STATUS_PENDING,
            'uploaded_by' => $user->id,
        ]);

        ActivityLog::record(
            activityType: 'dokumen_upload',
            userId: $user->id,
            subjectType: Document::class,
            subjectId: $document->id,
            description: "Pegawai {$user->name} mengupload dokumen baru.",
        );

        return response()->json([
            'message' => 'Dokumen berhasil diupload dan menunggu persetujuan admin.',
            'document' => $this->formatDocument($document->load('certificationType.category')),
        ], 201);
    }

    /**
     * Edit data dokumen (re-trigger pending_approval).
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        $user = $request->user();

        if ($document->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'implementation_date' => ['nullable', 'date'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['required', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $oldValues = $document->only([
            'certificate_number', 'implementation_date', 'issued_date', 'expiry_date',
        ]);

        // Jika ada file baru, simpan versi lama dulu
        if ($request->hasFile('file') && $document->file_path) {
            DocumentVersion::create([
                'document_id' => $document->id,
                'file_path' => $document->file_path,
                'file_name' => $document->file_name,
                'file_size' => $document->file_size,
                'file_mime' => $document->file_mime,
                'replaced_by' => $user->id,
                'replaced_at' => now(),
            ]);

            $file = $request->file('file');
            $document->file_path = $file->store('documents', 'public');
            $document->file_name = $file->getClientOriginalName();
            $document->file_size = $file->getSize();
            $document->file_mime = $file->getMimeType();
        }

        $document->certificate_number = $validated['certificate_number'] ?? $document->certificate_number;
        $document->implementation_date = $validated['implementation_date'] ?? $document->implementation_date;
        $document->issued_date = $validated['issued_date'] ?? $document->issued_date;
        $document->expiry_date = $validated['expiry_date'];
        $document->status = Document::STATUS_PENDING;
        $document->approved_by = null;
        $document->approved_at = null;
        $document->rejection_reason = null;
        $document->save();

        ActivityLog::record(
            activityType: 'dokumen_edit',
            userId: $user->id,
            subjectType: Document::class,
            subjectId: $document->id,
            description: "Pegawai {$user->name} mengedit dokumen.",
            oldValues: $oldValues,
            newValues: $document->only([
                'certificate_number', 'implementation_date', 'issued_date', 'expiry_date',
            ]),
        );

        return response()->json([
            'message' => 'Dokumen diperbarui dan menunggu persetujuan ulang dari admin.',
            'document' => $this->formatDocument($document->load('certificationType.category')),
        ]);
    }

    /**
     * Hapus dokumen.
     */
    public function destroy(Request $request, Document $document): JsonResponse
    {
        $user = $request->user();

        if ($document->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Hapus file dari storage
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Hapus file versi lama juga
        foreach ($document->versions as $version) {
            Storage::disk('public')->delete($version->file_path);
        }

        ActivityLog::record(
            activityType: 'dokumen_hapus',
            userId: $user->id,
            subjectType: Document::class,
            subjectId: $document->id,
            description: "Pegawai {$user->name} menghapus dokumen.",
        );

        $document->delete();

        return response()->json(['message' => 'Dokumen berhasil dihapus.']);
    }

    /**
     * Download file dokumen.
     */
    public function download(Request $request, Document $document)
    {
        if ($document->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (! $document->file_path || ! Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Export dokumen milik pegawai ke Excel.
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');

        $ownerName = Str::slug($user->name, '-');
        $suffix = $status ? "-{$status}" : '';
        $filename = "dokumen-{$ownerName}{$suffix}-".now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new MyDocumentsExport(userId: $user->id, status: $status ?: null),
            $filename
        );
    }

    /**
     * Format response dokumen.
     */
    private function formatDocument(Document $document, bool $detailed = false): array
    {
        $data = [
            'id' => $document->id,
            'certificate_number' => $document->certificate_number,
            'implementation_date' => $document->implementation_date?->format('Y-m-d'),
            'issued_date' => $document->issued_date?->format('Y-m-d'),
            'expiry_date' => $document->expiry_date?->format('Y-m-d'),
            'status' => $document->status,
            'rejection_reason' => $document->rejection_reason,
            'approved_at' => $document->approved_at?->format('Y-m-d H:i'),
            'has_file' => ! is_null($document->file_path),
            'file_path' => $document->file_path,
            'file_mime' => $document->file_mime,
            'file_name' => $document->file_name,
            'file_size' => $document->file_size,
            'created_at' => $document->created_at?->format('Y-m-d H:i'),
            'updated_at' => $document->updated_at?->format('Y-m-d H:i'),
            'certification_type' => $document->relationLoaded('certificationType') ? [
                'id' => $document->certificationType->id,
                'name' => $document->certificationType->name,
                'category' => [
                    'id' => $document->certificationType->category->id,
                    'name' => $document->certificationType->category->name,
                    'slug' => $document->certificationType->category->slug,
                ],
            ] : null,
        ];

        if ($detailed) {
            $data['approver'] = $document->approver ? $document->approver->name : null;
            $data['versions'] = $document->versions->map(fn ($v) => [
                'id' => $v->id,
                'file_name' => $v->file_name,
                'file_size' => $v->file_size,
                'replaced_at' => $v->replaced_at?->format('Y-m-d H:i'),
            ]);
        }

        return $data;
    }
}
