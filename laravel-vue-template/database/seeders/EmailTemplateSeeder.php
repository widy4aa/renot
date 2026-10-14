<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Seed template email default untuk semua tipe notifikasi.
     * Menggunakan updateOrCreate agar aman dijalankan berulang kali.
     *
     * Placeholder yang tersedia di semua template:
     *   {{nama}}               - Nama lengkap pegawai
     *   {{sertifikasi}}        - Nama jenis sertifikasi
     *   {{tanggal_kadaluarsa}} - Tanggal kadaluarsa (format dd/mm/yyyy)
     *   {{hari_tersisa}}       - Sisa hari sebelum kadaluarsa
     *   {{departemen}}         - Nama departemen pegawai
     *   {{nomor_sertifikat}}   - Nomor sertifikat dokumen
     *   {{alasan_penolakan}}   - Alasan penolakan (hanya untuk dokumen_ditolak)
     */
    public function run(): void
    {
        $templates = [
            [
                'type' => 'reminder_akan_expired',
                'subject' => '[ReNot] Reminder: Sertifikasi {{sertifikasi}} Akan Kadaluarsa dalam {{hari_tersisa}} Hari',
                'body' => "Yth. {{nama}},\n\nIni adalah pengingat bahwa dokumen sertifikasi Anda akan segera kadaluarsa.\n\nDetail Sertifikasi:\n- Jenis Sertifikasi : {{sertifikasi}}\n- Nomor Sertifikat  : {{nomor_sertifikat}}\n- Tanggal Kadaluarsa: {{tanggal_kadaluarsa}}\n- Sisa Hari        : {{hari_tersisa}} hari\n\nMohon segera memperbarui sertifikasi Anda sebelum tanggal kadaluarsa agar status tetap aktif.\n\nSilakan login ke sistem ReNot untuk mengunggah dokumen pembaruan.\n\nHormat kami,\nTim ReNot – Pertamina",
                'placeholders' => '{{nama}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{tanggal_kadaluarsa}}, {{hari_tersisa}}, {{departemen}}',
            ],
            [
                'type' => 'dokumen_expired',
                'subject' => '[ReNot] Sertifikasi {{sertifikasi}} Anda Telah Kadaluarsa',
                'body' => "Yth. {{nama}},\n\nKami menginformasikan bahwa dokumen sertifikasi Anda telah melewati tanggal kadaluarsa.\n\nDetail Sertifikasi:\n- Jenis Sertifikasi : {{sertifikasi}}\n- Nomor Sertifikat  : {{nomor_sertifikat}}\n- Tanggal Kadaluarsa: {{tanggal_kadaluarsa}}\n\nStatus sertifikasi Anda saat ini telah diubah menjadi Kadaluarsa. Mohon segera melakukan pembaruan sertifikasi dan mengunggah dokumen baru ke sistem ReNot.\n\nHormat kami,\nTim ReNot – Pertamina",
                'placeholders' => '{{nama}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{tanggal_kadaluarsa}}, {{departemen}}',
            ],
            [
                'type' => 'dokumen_approved',
                'subject' => '[ReNot] Dokumen Sertifikasi {{sertifikasi}} Anda Telah Disetujui',
                'body' => "Yth. {{nama}},\n\nKami dengan senang menginformasikan bahwa dokumen sertifikasi Anda telah diverifikasi dan disetujui oleh administrator.\n\nDetail Sertifikasi:\n- Jenis Sertifikasi : {{sertifikasi}}\n- Nomor Sertifikat  : {{nomor_sertifikat}}\n- Tanggal Kadaluarsa: {{tanggal_kadaluarsa}}\n\nDokumen Anda kini berstatus Aktif di sistem ReNot.\n\nHormat kami,\nTim ReNot – Pertamina",
                'placeholders' => '{{nama}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{tanggal_kadaluarsa}}, {{departemen}}',
            ],
            [
                'type' => 'dokumen_ditambahkan',
                'subject' => '[ReNot] Dokumen Sertifikasi {{sertifikasi}} Telah Ditambahkan untuk Anda',
                'body' => "Yth. {{nama}},\n\nKami menginformasikan bahwa administrator telah menambahkan dokumen sertifikasi baru untuk Anda ke dalam sistem ReNot.\n\nDetail Sertifikasi:\n- Jenis Sertifikasi : {{sertifikasi}}\n- Nomor Sertifikat  : {{nomor_sertifikat}}\n- Tanggal Kadaluarsa: {{tanggal_kadaluarsa}}\n\nDokumen sertifikasi Anda telah aktif dan tercatat pada profil Anda.\n\nSilakan login ke sistem ReNot untuk melihat detail dokumen.\n\nHormat kami,\nTim ReNot – Pertamina",
                'placeholders' => '{{nama}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{tanggal_kadaluarsa}}, {{departemen}}',
            ],
            [
                'type' => 'dokumen_ditolak',
                'subject' => '[ReNot] Dokumen Sertifikasi {{sertifikasi}} Anda Ditolak',
                'body' => "Yth. {{nama}},\n\nKami menginformasikan bahwa dokumen sertifikasi yang Anda unggah tidak dapat disetujui oleh administrator.\n\nDetail Sertifikasi:\n- Jenis Sertifikasi : {{sertifikasi}}\n- Nomor Sertifikat  : {{nomor_sertifikat}}\n\nAlasan Penolakan:\n{{alasan_penolakan}}\n\nMohon perbaiki dokumen sesuai alasan di atas dan unggah kembali melalui sistem ReNot.\n\nHormat kami,\nTim ReNot – Pertamina",
                'placeholders' => '{{nama}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{alasan_penolakan}}, {{departemen}}',
            ],
            [
                'type' => 'dokumen_pending',
                'subject' => '[ReNot] Dokumen Sertifikasi Baru Menunggu Verifikasi',
                'body' => "Kepada Administrator ReNot,\n\nTerdapat dokumen sertifikasi baru yang diunggah oleh pegawai dan memerlukan verifikasi.\n\nDetail Sertifikasi:\n- Pegawai           : {{nama}}\n- Departemen        : {{departemen}}\n- Jenis Sertifikasi : {{sertifikasi}}\n- Nomor Sertifikat  : {{nomor_sertifikat}}\n- Tanggal Kadaluarsa: {{tanggal_kadaluarsa}}\n\nSilakan login ke sistem ReNot untuk meninjau dan memverifikasi dokumen tersebut.\n\nHormat kami,\nSistem ReNot – Pertamina",
                'placeholders' => '{{nama}}, {{departemen}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{tanggal_kadaluarsa}}',
            ],
            [
                'type' => 'registrasi_approved',
                'subject' => '[ReNot] Akun Anda Telah Disetujui',
                'body' => "Yth. {{nama}},\n\nSelamat! Pendaftaran akun ReNot Anda telah disetujui oleh administrator.\n\nAnda sekarang dapat login ke sistem ReNot menggunakan email dan kata sandi yang Anda daftarkan.\n\nDetail Akun:\n- Nama       : {{nama}}\n- Email      : {{email}}\n- NIP        : {{nip}}\n- Departemen : {{departemen}}\n\nSilakan login dan mulai mengunggah dokumen sertifikasi Anda.\n\nHormat kami,\nTim ReNot – Pertamina",
                'placeholders' => '{{nama}}, {{email}}, {{nip}}, {{departemen}}',
            ],
            [
                'type' => 'registrasi_rejected',
                'subject' => '[ReNot] Pendaftaran Akun Ditolak',
                'body' => "Yth. {{nama}},\n\nKami menginformasikan bahwa pendaftaran akun ReNot Anda tidak dapat disetujui oleh administrator.\n\nAlasan Penolakan:\n{{alasan_penolakan}}\n\nJika Anda merasa ini adalah kekeliruan, silakan mendaftar ulang atau hubungi administrator.\n\nHormat kami,\nTim ReNot – Pertamina",
                'placeholders' => '{{nama}}, {{email}}, {{nip}}, {{departemen}}, {{alasan_penolakan}}',
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['type' => $template['type']],
                [
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'placeholders' => $template['placeholders'],
                ],
            );
        }
    }
}
