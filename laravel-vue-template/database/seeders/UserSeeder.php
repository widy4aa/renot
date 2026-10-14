<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    /**
     * Seed admin dan pegawai dummy.
     *
     * Urutan:
     * 1. Admin (tidak perlu departemen)
     * 2. Pegawai (butuh departemen + created_by admin)
     *
     * Semua password: "password"
     * Avatar: diambil dari folder sertif/ dan di-copy ke storage/app/public/avatars/
     */
    public function run(): void
    {
        // Pastikan direktori avatar ada
        Storage::disk('public')->makeDirectory('avatars');

        // ── Siapkan avatar dari folder sertif/ ─────────────
        $sertifPath = base_path('../sertif');
        $avatarFiles = [];

        $dummyAvatars = [
            'images (2).jpeg',   // kecil, cocok untuk avatar
            'images (3).jpeg',
            'images.jpeg',
        ];

        foreach ($dummyAvatars as $filename) {
            $source = $sertifPath.'/'.$filename;
            if (File::exists($source)) {
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $storageName = 'avatar_'.md5($filename).'.'.$ext;
                $storagePath = 'avatars/'.$storageName;

                Storage::disk('public')->put($storagePath, File::get($source));
                $avatarFiles[] = $storagePath;
            }
        }

        // ── Ambil ID departemen ─────────────────────────────
        $hsseId = DB::table('departments')->where('name', 'HSSE')->value('id');
        $aviasiId = DB::table('departments')->where('name', 'Aviasi')->value('id');
        $operasionalId = DB::table('departments')->where('name', 'Operasional')->value('id');

        // ── 1. Admin ────────────────────────────────────────
        $adminId = DB::table('users')->insertGetId([
            'employee_number' => null,
            'name' => 'Administrator',
            'email' => 'admin@renot.app',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081200000000',
            'avatar' => null,
            'department_id' => null,
            'is_active' => true,
            'registration_status' => 'approved',
            'registered_at' => null,
            'email_verified_at' => now(),
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── 2. Pegawai ──────────────────────────────────────
        $pegawai = [
            [
                'employee_number' => 'EMP-001',
                'name' => 'Budi Santoso',
                'email' => 'budi@renot.app',
                'phone' => '081211111111',
                'department_id' => $hsseId,
                'avatar' => $avatarFiles[0] ?? null,
            ],
            [
                'employee_number' => 'EMP-002',
                'name' => 'Siti Rahayu',
                'email' => 'siti@renot.app',
                'phone' => '081222222222',
                'department_id' => $aviasiId,
                'avatar' => $avatarFiles[1] ?? null,
            ],
            [
                'employee_number' => 'EMP-003',
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@renot.app',
                'phone' => '081233333333',
                'department_id' => $operasionalId,
                'avatar' => $avatarFiles[2] ?? null,
            ],
        ];

        foreach ($pegawai as $user) {
            DB::table('users')->insert([
                'employee_number' => $user['employee_number'],
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
                'role' => 'pegawai',
                'phone' => $user['phone'],
                'avatar' => $user['avatar'],
                'department_id' => $user['department_id'],
                'is_active' => true,
                'registration_status' => 'approved',
                'registered_at' => null,
                'email_verified_at' => now(),
                'created_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
