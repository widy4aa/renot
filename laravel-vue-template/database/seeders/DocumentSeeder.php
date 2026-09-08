<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('role', 'admin')->value('id');

        $budi   = DB::table('users')->where('email', 'budi@renot.app')->first();
        $siti   = DB::table('users')->where('email', 'siti@renot.app')->first();
        $ahmad  = DB::table('users')->where('email', 'ahmad@renot.app')->first();

        // Certification type IDs
        $types = DB::table('certification_types')
            ->get()
            ->keyBy('name');

        $now = Carbon::now();

        /**
         * Budi Santoso — Departemen HSSE
         * Fokus sertifikasi HSSE, sudah lama kerja, punya banyak dok
         */
        $budidocs = [
            // GSI — aktif, masih lama
            [
                'user_id'               => $budi->id,
                'certification_type_id' => $types['GSI']->id,
                'certificate_number'    => 'GSI/2024/0312',
                'implementation_date'   => '2024-03-10',
                'issued_date'           => '2024-03-12',
                'expiry_date'           => $now->copy()->addDays(280)->toDateString(),
                'status'                => 'aktif',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(300),
            ],
            // SI — segera expired (H-45)
            [
                'user_id'               => $budi->id,
                'certification_type_id' => $types['SI']->id,
                'certificate_number'    => 'SI/2023/0887',
                'implementation_date'   => '2023-09-05',
                'issued_date'           => '2023-09-08',
                'expiry_date'           => $now->copy()->addDays(45)->toDateString(),
                'status'                => 'segera_expired',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(350),
            ],
            // AT — segera expired (H-10), urgen
            [
                'user_id'               => $budi->id,
                'certification_type_id' => $types['AT']->id,
                'certificate_number'    => 'AT/2023/0214',
                'implementation_date'   => '2023-02-12',
                'issued_date'           => '2023-02-14',
                'expiry_date'           => $now->copy()->addDays(10)->toDateString(),
                'status'                => 'segera_expired',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(360),
            ],
            // HSSE Passport — expired
            [
                'user_id'               => $budi->id,
                'certification_type_id' => $types['HSSE Passport']->id,
                'certificate_number'    => 'HSSEP/2022/0055',
                'implementation_date'   => '2022-06-01',
                'issued_date'           => '2022-06-03',
                'expiry_date'           => $now->copy()->subDays(45)->toDateString(),
                'status'                => 'expired',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(820),
            ],
            // Proper — pending approval, baru upload
            [
                'user_id'               => $budi->id,
                'certification_type_id' => $types['Proper']->id,
                'certificate_number'    => 'PROP/2026/0091',
                'implementation_date'   => '2026-08-20',
                'issued_date'           => '2026-08-25',
                'expiry_date'           => $now->copy()->addDays(700)->toDateString(),
                'status'                => 'pending_approval',
                'uploaded_by'           => $budi->id,
                'approved_by'           => null,
                'approved_at'           => null,
            ],
            // Lainnya (HSSE) — ditolak, perlu upload ulang
            [
                'user_id'               => $budi->id,
                'certification_type_id' => $types['Lainnya']->id,
                'certificate_number'    => 'HSE-LN/2026/0012',
                'implementation_date'   => '2026-07-10',
                'issued_date'           => '2026-07-15',
                'expiry_date'           => $now->copy()->addDays(400)->toDateString(),
                'status'                => 'ditolak',
                'rejection_reason'      => 'File yang diupload buram dan tidak terbaca. Mohon upload ulang dengan scan yang lebih jelas.',
                'uploaded_by'           => $budi->id,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(3),
            ],
        ];

        /**
         * Siti Rahayu — Departemen Aviasi
         * Fokus sertifikasi Aviasi, juga punya beberapa HSSE
         */
        $sitidocs = [
            // RDS — aktif
            [
                'user_id'               => $siti->id,
                'certification_type_id' => $types['RDS']->id,
                'certificate_number'    => 'RDS/2025/0441',
                'implementation_date'   => '2025-01-15',
                'issued_date'           => '2025-01-18',
                'expiry_date'           => $now->copy()->addDays(200)->toDateString(),
                'status'                => 'aktif',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(240),
            ],
            // PACE — segera expired (H-30)
            [
                'user_id'               => $siti->id,
                'certification_type_id' => $types['PACE']->id,
                'certificate_number'    => 'PACE/2024/0772',
                'implementation_date'   => '2024-08-28',
                'issued_date'           => '2024-09-01',
                'expiry_date'           => $now->copy()->addDays(30)->toDateString(),
                'status'                => 'segera_expired',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(370),
            ],
            // GSI — aktif
            [
                'user_id'               => $siti->id,
                'certification_type_id' => $types['GSI']->id,
                'certificate_number'    => 'GSI/2025/0109',
                'implementation_date'   => '2025-03-20',
                'issued_date'           => '2025-03-22',
                'expiry_date'           => $now->copy()->addDays(180)->toDateString(),
                'status'                => 'aktif',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(160),
            ],
            // HSSE Passport — expired
            [
                'user_id'               => $siti->id,
                'certification_type_id' => $types['HSSE Passport']->id,
                'certificate_number'    => 'HSSEP/2023/0188',
                'implementation_date'   => '2023-04-10',
                'issued_date'           => '2023-04-12',
                'expiry_date'           => $now->copy()->subDays(10)->toDateString(),
                'status'                => 'expired',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(730),
            ],
            // Aviasi Lainnya — pending approval
            [
                'user_id'               => $siti->id,
                'certification_type_id' => $types['Lainnya']->id,
                'certificate_number'    => 'AV-LN/2026/0033',
                'implementation_date'   => '2026-09-01',
                'issued_date'           => '2026-09-03',
                'expiry_date'           => $now->copy()->addDays(365)->toDateString(),
                'status'                => 'pending_approval',
                'uploaded_by'           => $siti->id,
                'approved_by'           => null,
                'approved_at'           => null,
            ],
        ];

        /**
         * Ahmad Fauzi — Departemen Operasional
         * Pegawai baru, dokumen masih sedikit
         */
        $ahmaddocs = [
            // GSI — aktif, baru terbit
            [
                'user_id'               => $ahmad->id,
                'certification_type_id' => $types['GSI']->id,
                'certificate_number'    => 'GSI/2026/0588',
                'implementation_date'   => '2026-07-01',
                'issued_date'           => '2026-07-05',
                'expiry_date'           => $now->copy()->addDays(665)->toDateString(),
                'status'                => 'aktif',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(65),
            ],
            // SI — pending, baru upload sendiri
            [
                'user_id'               => $ahmad->id,
                'certification_type_id' => $types['SI']->id,
                'certificate_number'    => 'SI/2026/0301',
                'implementation_date'   => '2026-08-30',
                'issued_date'           => '2026-09-02',
                'expiry_date'           => $now->copy()->addDays(730)->toDateString(),
                'status'                => 'pending_approval',
                'uploaded_by'           => $ahmad->id,
                'approved_by'           => null,
                'approved_at'           => null,
            ],
            // AT — expired, belum diperbarui
            [
                'user_id'               => $ahmad->id,
                'certification_type_id' => $types['AT']->id,
                'certificate_number'    => 'AT/2024/0930',
                'implementation_date'   => '2024-09-28',
                'issued_date'           => '2024-09-30',
                'expiry_date'           => $now->copy()->subDays(5)->toDateString(),
                'status'                => 'expired',
                'uploaded_by'           => $adminId,
                'approved_by'           => $adminId,
                'approved_at'           => $now->copy()->subDays(340),
            ],
        ];

        $allDocs = array_merge($budidocs, $sitidocs, $ahmaddocs);

        foreach ($allDocs as $doc) {
            DB::table('documents')->insert(array_merge($doc, [
                'file_path'  => null,
                'file_name'  => null,
                'file_size'  => null,
                'file_mime'  => null,
                'created_at' => $doc['approved_at'] ?? $now->copy()->subDays(1),
                'updated_at' => $now,
            ]));
        }
    }
}
