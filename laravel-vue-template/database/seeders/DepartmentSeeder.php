<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'HSSE',
            'Aviasi',
            'Operasional',
            'Teknik',
            'Administrasi',
        ];

        foreach ($departments as $name) {
            DB::table('departments')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
