<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hsseId = DB::table('departments')->where('name', 'HSSE')->value('id');
        $aviasiId = DB::table('departments')->where('name', 'Aviasi')->value('id');
        $operasionalId = DB::table('departments')->where('name', 'Operasional')->value('id');

        // Admin
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
            'email_verified_at' => now(),
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3 Pegawai
        $pegawai = [
            [
                'employee_number' => 'EMP-001',
                'name' => 'Budi Santoso',
                'email' => 'budi@renot.app',
                'phone' => '081211111111',
                'department_id' => $hsseId,
            ],
            [
                'employee_number' => 'EMP-002',
                'name' => 'Siti Rahayu',
                'email' => 'siti@renot.app',
                'phone' => '081222222222',
                'department_id' => $aviasiId,
            ],
            [
                'employee_number' => 'EMP-003',
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@renot.app',
                'phone' => '081233333333',
                'department_id' => $operasionalId,
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
                'avatar' => null,
                'department_id' => $user['department_id'],
                'is_active' => true,
                'email_verified_at' => now(),
                'created_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
