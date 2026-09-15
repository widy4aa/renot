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

class AdminDocumentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = []) {}

    public function collection(): Enumerable
    {
        $query = Document::with(['owner.department', 'certificationType.category'])
            ->orderBy('expiry_date');

        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (! empty($this->filters['departemen'])) {
            $query->whereHas('owner', fn ($q) => $q->where('department_id', $this->filters['departemen']));
        }
        if (! empty($this->filters['kategori'])) {
            $query->whereHas('certificationType.category', fn ($q) => $q->where('name', $this->filters['kategori']));
        }
        if (! empty($this->filters['jenis'])) {
            $query->whereHas('certificationType', fn ($q) => $q->where('name', $this->filters['jenis']));
        }
        if (! empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(fn ($q) => $q
                ->where('certificate_number', 'ilike', "%{$s}%")
                ->orWhereHas('owner', fn ($q2) => $q2->where('name', 'ilike', "%{$s}%"))
                ->orWhereHas('certificationType', fn ($q2) => $q2->where('name', 'ilike', "%{$s}%"))
            );
        }
        if (! empty($this->filters['exp_from'])) {
            $query->whereDate('expiry_date', '>=', $this->filters['exp_from']);
        }
        if (! empty($this->filters['exp_to'])) {
            $query->whereDate('expiry_date', '<=', $this->filters['exp_to']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No. Pegawai',
            'Nama Pegawai',
            'Departemen',
            'Kategori Sertifikasi',
            'Jenis Dokumen',
            'Nomor Sertifikat',
            'Tgl. Pelaksanaan',
            'Tgl. Terbit',
            'Tgl. Kadaluarsa',
            'Status',
            'Disetujui Oleh',
            'Tgl. Approval',
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
            $document->approver?->name ?? '-',
            $document->approved_at?->format('d/m/Y H:i') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'ED1B2F']],
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
