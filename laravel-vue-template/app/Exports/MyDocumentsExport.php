<?php

namespace App\Exports;

use App\Models\Document;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MyDocumentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly int $userId,
        private readonly ?string $status = null,
    ) {}

    public function collection(): Enumerable
    {
        $query = Document::where('user_id', $this->userId)
            ->with(['owner.department', 'certificationType.category'])
            ->orderBy('expiry_date');

        if ($this->status) {
            $query->where('status', $this->status);
        } else {
            // Terbaru per jenis sertifikasi
            $query->orderByDesc('created_at');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No. Pegawai',
            'Nama Pegawai',
            'Departemen',
            'Jenis Dokumen',
            'Kategori Sertifikasi',
            'Nomor Sertifikat',
            'Tgl. Pelaksanaan',
            'Tgl. Terbit',
            'Tgl. Kadaluarsa',
            'Status',
        ];
    }

    public function map($document): array
    {
        return [
            $document->owner->employee_number ?? '-',
            $document->owner->name,
            $document->owner->department?->name ?? '-',
            $document->certificationType->category->name,
            $document->certificationType->name,
            $document->certificate_number ?? '-',
            $document->implementation_date?->format('d/m/Y') ?? '-',
            $document->issued_date?->format('d/m/Y') ?? '-',
            $document->expiry_date->format('d/m/Y'),
            $this->statusLabel($document->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '16A34A']],
            ],
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'aktif' => 'Aktif',
            'segera_expired' => 'Segera Expired',
            'expired' => 'Expired',
            'pending_approval' => 'Pending Approval',
            'ditolak' => 'Ditolak',
            default => $status,
        };
    }
}
