<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $budi  = DB::table('users')->where('email', 'budi@renot.app')->first();
        $siti  = DB::table('users')->where('email', 'siti@renot.app')->first();
        $ahmad = DB::table('users')->where('email', 'ahmad@renot.app')->first();
        $admin = DB::table('users')->where('role', 'admin')->first();

        // Ambil dokumen per user untuk referensi
        $budiDocs  = DB::table('documents')->where('user_id', $budi->id)->get()->keyBy('certification_type_id');
        $sitiDocs  = DB::table('documents')->where('user_id', $siti->id)->get()->keyBy('certification_type_id');
        $ahmadDocs = DB::table('documents')->where('user_id', $ahmad->id)->get()->keyBy('certification_type_id');

        $types = DB::table('certification_types')->get()->keyBy('id');

        $notifications = [

            // ── Budi Santoso ──────────────────────────────────────────

            // AT hampir expired H-10 → urgen
            [
                'user_id'     => $budi->id,
                'document_id' => $budiDocs[3]->id ?? null, // AT
                'type'        => 'reminder_akan_expired',
                'channel'     => 'in_app',
                'title'       => 'Sertifikat AT akan expired dalam 10 hari',
                'body'        => 'Sertifikat AT (No. AT/2023/0214) Anda akan berakhir pada ' . $now->copy()->addDays(10)->format('d M Y') . '. Segera perbarui.',
                'is_read'     => false,
                'created_at'  => $now->copy()->subHours(2),
            ],
            // SI hampir expired H-45
            [
                'user_id'     => $budi->id,
                'document_id' => $budiDocs[2]->id ?? null, // SI
                'type'        => 'reminder_akan_expired',
                'channel'     => 'in_app',
                'title'       => 'Sertifikat SI akan expired dalam 45 hari',
                'body'        => 'Sertifikat SI (No. SI/2023/0887) Anda akan berakhir pada ' . $now->copy()->addDays(45)->format('d M Y') . '. Harap segera siapkan perpanjangan.',
                'is_read'     => false,
                'created_at'  => $now->copy()->subHours(6),
            ],
            // HSSE Passport sudah expired
            [
                'user_id'     => $budi->id,
                'document_id' => $budiDocs[4]->id ?? null, // HSSE Passport
                'type'        => 'dokumen_expired',
                'channel'     => 'in_app',
                'title'       => 'Sertifikat HSSE Passport telah expired',
                'body'        => 'Sertifikat HSSE Passport (No. HSSEP/2022/0055) Anda sudah melewati tanggal kadaluarsa. Segera lakukan pembaruan.',
                'is_read'     => true,
                'created_at'  => $now->copy()->subDays(3),
            ],
            // Proper ditolak admin
            [
                'user_id'     => $budi->id,
                'document_id' => $budiDocs[6]->id ?? null, // Lainnya/ditolak
                'type'        => 'dokumen_ditolak',
                'channel'     => 'in_app',
                'title'       => 'Dokumen Lainnya (HSSE) ditolak',
                'body'        => 'Dokumen Anda ditolak oleh admin. Alasan: File yang diupload buram dan tidak terbaca. Mohon upload ulang.',
                'is_read'     => false,
                'created_at'  => $now->copy()->subDays(3)->addHours(1),
            ],
            // Proper pending
            [
                'user_id'     => $budi->id,
                'document_id' => $budiDocs[5]->id ?? null, // Proper pending
                'type'        => 'dokumen_pending',
                'channel'     => 'in_app',
                'title'       => 'Dokumen Proper sedang direview',
                'body'        => 'Dokumen Proper (No. PROP/2026/0091) yang Anda upload sedang menunggu persetujuan admin.',
                'is_read'     => true,
                'created_at'  => $now->copy()->subDays(5),
            ],

            // ── Siti Rahayu ──────────────────────────────────────────

            // PACE hampir expired H-30
            [
                'user_id'     => $siti->id,
                'document_id' => $sitiDocs[8]->id ?? null, // PACE
                'type'        => 'reminder_akan_expired',
                'channel'     => 'in_app',
                'title'       => 'Sertifikat PACE akan expired dalam 30 hari',
                'body'        => 'Sertifikat PACE (No. PACE/2024/0772) Anda akan berakhir pada ' . $now->copy()->addDays(30)->format('d M Y') . '. Harap segera siapkan perpanjangan.',
                'is_read'     => false,
                'created_at'  => $now->copy()->subHours(4),
            ],
            // HSSE Passport expired
            [
                'user_id'     => $siti->id,
                'document_id' => $sitiDocs[4]->id ?? null, // HSSE Passport
                'type'        => 'dokumen_expired',
                'channel'     => 'in_app',
                'title'       => 'Sertifikat HSSE Passport telah expired',
                'body'        => 'Sertifikat HSSE Passport (No. HSSEP/2023/0188) Anda sudah melewati tanggal kadaluarsa 10 hari yang lalu.',
                'is_read'     => false,
                'created_at'  => $now->copy()->subDays(1),
            ],
            // Aviasi Lainnya pending
            [
                'user_id'     => $siti->id,
                'document_id' => null,
                'type'        => 'dokumen_pending',
                'channel'     => 'in_app',
                'title'       => 'Dokumen Aviasi Lainnya sedang direview',
                'body'        => 'Dokumen Aviasi Lainnya (No. AV-LN/2026/0033) yang Anda upload sedang menunggu persetujuan admin.',
                'is_read'     => true,
                'created_at'  => $now->copy()->subDays(2),
            ],

            // ── Ahmad Fauzi ──────────────────────────────────────────

            // AT expired
            [
                'user_id'     => $ahmad->id,
                'document_id' => $ahmadDocs[3]->id ?? null, // AT
                'type'        => 'dokumen_expired',
                'channel'     => 'in_app',
                'title'       => 'Sertifikat AT telah expired',
                'body'        => 'Sertifikat AT (No. AT/2024/0930) Anda sudah melewati tanggal kadaluarsa. Segera lakukan pembaruan.',
                'is_read'     => false,
                'created_at'  => $now->copy()->subDays(1)->subHours(3),
            ],
            // SI pending
            [
                'user_id'     => $ahmad->id,
                'document_id' => null,
                'type'        => 'dokumen_pending',
                'channel'     => 'in_app',
                'title'       => 'Dokumen SI sedang direview',
                'body'        => 'Dokumen SI (No. SI/2026/0301) yang Anda upload sedang menunggu persetujuan admin.',
                'is_read'     => false,
                'created_at'  => $now->copy()->subHours(10),
            ],

            // ── Admin — notifikasi kelola dokumen ──────────────────

            [
                'user_id'     => $admin->id,
                'document_id' => null,
                'type'        => 'dokumen_pending',
                'channel'     => 'in_app',
                'title'       => 'Dokumen baru menunggu approval (Budi Santoso)',
                'body'        => 'Budi Santoso mengupload dokumen Proper baru. Segera review dan setujui.',
                'is_read'     => false,
                'created_at'  => $now->copy()->subDays(5),
            ],
            [
                'user_id'     => $admin->id,
                'document_id' => null,
                'type'        => 'dokumen_pending',
                'channel'     => 'in_app',
                'title'       => 'Dokumen baru menunggu approval (Ahmad Fauzi)',
                'body'        => 'Ahmad Fauzi mengupload dokumen SI baru. Segera review dan setujui.',
                'is_read'     => false,
                'created_at'  => $now->copy()->subHours(10),
            ],
            [
                'user_id'     => $admin->id,
                'document_id' => null,
                'type'        => 'dokumen_expired',
                'channel'     => 'in_app',
                'title'       => 'Sertifikat AT Ahmad Fauzi telah expired',
                'body'        => 'Sertifikat AT milik Ahmad Fauzi (No. AT/2024/0930) sudah melewati tanggal kadaluarsa.',
                'is_read'     => true,
                'created_at'  => $now->copy()->subDays(5),
            ],
        ];

        foreach ($notifications as $notif) {
            DB::table('notifications')->insert(array_merge($notif, [
                'read_at'  => $notif['is_read'] ? $now->copy()->subHours(1) : null,
                'sent_at'  => $notif['created_at'],
            ]));
        }
    }
}
