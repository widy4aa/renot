<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminDocumentsExport;
use App\Http\Controllers\Controller;
use App\Mail\ReminderMail;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DocumentController extends Controller
{
    /**
     * Daftar semua dokumen seluruh pegawai (server-side, all fields).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::with([
            'certificationType.category',
            'owner:id,name,employee_number,department_id',
            'owner.department:id,name',
        ])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('departemen')) {
            $query->whereHas('owner', fn ($q) => $q->where('department_id', $request->departemen));
        }
        if ($request->filled('kategori')) {
            $query->whereHas('certificationType.category', fn ($q) => $q->where('name', $request->kategori));
        }
        if ($request->filled('jenis')) {
            $query->whereHas('certificationType', fn ($q) => $q->where('name', $request->jenis));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('certificate_number', 'ilike', "%{$s}%")
                ->orWhereHas('owner', fn ($q2) => $q2->where('name', 'ilike', "%{$s}%"))
                ->orWhereHas('certificationType', fn ($q2) => $q2->where('name', 'ilike', "%{$s}%"))
            );
        }
        if ($request->filled('exp_from')) {
            $query->whereDate('expiry_date', '>=', $request->exp_from);
        }
        if ($request->filled('exp_to')) {
            $query->whereDate('expiry_date', '<=', $request->exp_to);
        }

        $docs = $query->get();

        return response()->json([
            'data' => $docs->map(fn ($d) => $this->formatDocument($d)),
        ]);
    }

    /**
     * Admin tambah dokumen untuk pegawai tertentu (langsung aktif).
     */
    public function store(Request $request): JsonResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'certification_type_id' => ['required', 'exists:certification_types,id'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'implementation_date' => ['nullable', 'date'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['required', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $filePath = $fileName = $fileSize = $fileMime = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('documents', 'public');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileMime = $file->getMimeType();
        }

        $document = Document::create([
            'user_id' => $validated['user_id'],
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
            'uploaded_by' => $admin->id,
        ]);

        // Admin input langsung → auto approve
        $document->status = $document->computeStatus();
        $document->approved_by = $admin->id;
        $document->approved_at = now();
        $document->save();

        ActivityLog::record(
            activityType: 'dokumen_upload',
            userId: $admin->id,
            subjectType: Document::class,
            subjectId: $document->id,
            description: "Admin {$admin->name} menambah dokumen untuk pegawai ID {$validated['user_id']}.",
        );

        return response()->json([
            'message' => 'Dokumen berhasil ditambahkan.',
            'document' => $this->formatDocument($document->load('certificationType.category', 'owner.department')),
        ], 201);
    }

    /**
     * Detail satu dokumen.
     */
    public function show(Document $document): JsonResponse
    {
        $document->load(['certificationType.category', 'owner.department', 'approver', 'versions']);

        return response()->json($this->formatDocument($document, detailed: true));
    }

    /**
     * Edit dokumen (tidak re-trigger pending_approval, admin langsung aktif).
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        $admin = $request->user();

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

        if ($request->hasFile('file') && $document->file_path) {
            DocumentVersion::create([
                'document_id' => $document->id,
                'file_path' => $document->file_path,
                'file_name' => $document->file_name,
                'file_size' => $document->file_size,
                'file_mime' => $document->file_mime,
                'replaced_by' => $admin->id,
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

        // Jika sudah approved, recalculate status
        if ($document->isApproved()) {
            $document->status = $document->computeStatus();
        }

        $document->save();

        ActivityLog::record(
            activityType: 'dokumen_edit',
            userId: $admin->id,
            subjectType: Document::class,
            subjectId: $document->id,
            description: "Admin {$admin->name} mengedit dokumen ID {$document->id}.",
            oldValues: $oldValues,
            newValues: $document->only([
                'certificate_number', 'implementation_date', 'issued_date', 'expiry_date',
            ]),
        );

        return response()->json([
            'message' => 'Dokumen berhasil diperbarui.',
            'document' => $this->formatDocument($document->load('certificationType.category', 'owner.department')),
        ]);
    }

    /**
     * Hapus dokumen.
     */
    public function destroy(Request $request, Document $document): JsonResponse
    {
        $admin = $request->user();

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        foreach ($document->versions as $version) {
            Storage::disk('public')->delete($version->file_path);
        }

        ActivityLog::record(
            activityType: 'dokumen_hapus',
            userId: $admin->id,
            subjectType: Document::class,
            subjectId: $document->id,
            description: "Admin {$admin->name} menghapus dokumen ID {$document->id}.",
        );

        $document->delete();

        return response()->json(['message' => 'Dokumen berhasil dihapus.']);
    }

    /**
     * Download file dokumen.
     */
    public function download(Document $document)
    {
        if (! $document->file_path || ! Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Export Excel dokumen (mengikuti filter yang dikirim).
     */
    public function export(Request $request)
    {
        $filters = $request->only(['status', 'departemen', 'kategori', 'jenis', 'search', 'exp_from', 'exp_to']);
        $filename = 'semua-dokumen-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new AdminDocumentsExport($filters), $filename);
    }

    /**
     * Approve dokumen — status dihitung dari expiry_date.
     */
    public function approve(Request $request, Document $document): JsonResponse
    {
        $admin = $request->user();

        $document->status = $document->computeStatus();
        $document->approved_by = $admin->id;
        $document->approved_at = now();
        $document->rejection_reason = null;
        $document->save();

        // Notifikasi in-app untuk pegawai
        Notification::create([
            'user_id' => $document->user_id,
            'channel' => 'in_app',
            'type' => 'dokumen_approved',
            'title' => 'Dokumen Disetujui',
            'body' => "Dokumen sertifikasi {$document->certificationType?->name} Anda telah disetujui oleh admin.",
            'document_id' => $document->id,
            'is_read' => false,
        ]);

        // Kirim email notifikasi ke pegawai
        $this->sendEmailNotification($document, 'dokumen_approved');

        ActivityLog::record(
            activityType: 'dokumen_approve',
            userId: $admin->id,
            subjectType: Document::class,
            subjectId: $document->id,
            description: "Admin {$admin->name} menyetujui dokumen ID {$document->id}.",
        );

        return response()->json([
            'message' => 'Dokumen berhasil disetujui.',
            'document' => $this->formatDocument($document->load('certificationType.category', 'owner.department', 'approver')),
        ]);
    }

    /**
     * Tolak dokumen — alasan penolakan wajib diisi.
     */
    public function reject(Request $request, Document $document): JsonResponse
    {
        $admin = $request->user();

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $document->status = Document::STATUS_DITOLAK;
        $document->rejection_reason = $request->rejection_reason;
        $document->approved_by = null;
        $document->approved_at = null;
        $document->save();

        // Notifikasi in-app untuk pegawai
        Notification::create([
            'user_id' => $document->user_id,
            'channel' => 'in_app',
            'type' => 'dokumen_ditolak',
            'title' => 'Dokumen Ditolak',
            'body' => "Dokumen sertifikasi {$document->certificationType?->name} Anda ditolak. Alasan: {$request->rejection_reason}",
            'document_id' => $document->id,
            'is_read' => false,
        ]);

        // Kirim email notifikasi ke pegawai dengan alasan penolakan
        $this->sendEmailNotification($document, 'dokumen_ditolak', [
            '{{alasan_penolakan}}' => $request->rejection_reason,
        ]);

        ActivityLog::record(
            activityType: 'dokumen_reject',
            userId: $admin->id,
            subjectType: Document::class,
            subjectId: $document->id,
            description: "Admin {$admin->name} menolak dokumen ID {$document->id}. Alasan: {$request->rejection_reason}",
        );

        return response()->json([
            'message' => 'Dokumen berhasil ditolak.',
            'document' => $this->formatDocument($document->load('certificationType.category', 'owner.department')),
        ]);
    }

    /**
     * Kirim email ke pemilik dokumen berdasarkan tipe template.
     * Gagal secara silent — dicatat ke log, response tetap 200.
     *
     * @param  array<string, string>  $extraPlaceholders
     */
    private function sendEmailNotification(Document $document, string $type, array $extraPlaceholders = []): void
    {
        $owner = $document->relationLoaded('owner') ? $document->owner : $document->owner()->with('department')->first();

        if (! $owner || ! $owner->email) {
            return;
        }

        try {
            Mail::to($owner->email)->send(new ReminderMail($type, $document, $extraPlaceholders));
        } catch (\Throwable $e) {
            Log::error("[ReNot] Gagal kirim email {$type} ke {$owner->email} untuk dokumen ID {$document->id}: {$e->getMessage()}");
        }
    }

    /**
     * Format response dokumen (sama dengan pegawai + tambah owner info).
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
                ],
            ] : null,
            'user' => $document->relationLoaded('owner') ? [
                'id' => $document->owner->id,
                'name' => $document->owner->name,
                'employee_number' => $document->owner->employee_number,
                'department' => $document->owner->department ? [
                    'id' => $document->owner->department->id,
                    'name' => $document->owner->department->name,
                ] : null,
            ] : null,
        ];

        if ($detailed) {
            $data['approver'] = $document->relationLoaded('approver') ? $document->approver?->name : null;
            $data['versions'] = $document->relationLoaded('versions')
                ? $document->versions->map(fn ($v) => [
                    'id' => $v->id,
                    'file_name' => $v->file_name,
                    'file_size' => $v->file_size,
                    'replaced_at' => $v->replaced_at?->format('Y-m-d H:i'),
                ])
                : [];
        }

        return $data;
    }
}
