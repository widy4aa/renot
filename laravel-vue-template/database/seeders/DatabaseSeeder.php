<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan semua seeder dalam urutan yang benar.
     *
     * Urutan:
     * 1. Master data (tidak bergantung pada tabel lain)
     * 2. Konfigurasi sistem
     * 3. User (admin dulu, lalu pegawai)
     * 4. Data transaksional (dokumen, notifikasi)
     */
    public function run(): void
    {
        $this->call([
            // ── 1. Master Data ─────────────────────────────
            DepartmentSeeder::class,
            CertificationCategorySeeder::class,

            // ── 2. Konfigurasi Sistem ──────────────────────
            ReminderScheduleSeeder::class,
            EmailTemplateSeeder::class,

            // ── 3. User (admin → pegawai + avatar) ─────────
            UserSeeder::class,

            // ── 4. Data Transaksional ──────────────────────
            DocumentSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
