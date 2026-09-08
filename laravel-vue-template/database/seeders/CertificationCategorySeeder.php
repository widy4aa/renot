<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CertificationCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'HSSE',
                'slug' => 'hsse',
                'is_deletable' => false,
                'types' => [
                    ['name' => 'GSI', 'code' => 'GSI'],
                    ['name' => 'SI', 'code' => 'SI'],
                    ['name' => 'AT', 'code' => 'AT'],
                    ['name' => 'HSSE Passport', 'code' => 'HSSE-PASSPORT'],
                    ['name' => 'Proper', 'code' => 'PROPER'],
                    ['name' => 'Lainnya', 'code' => null],
                ],
            ],
            [
                'name' => 'Aviasi',
                'slug' => 'aviasi',
                'is_deletable' => false,
                'types' => [
                    ['name' => 'RDS', 'code' => 'RDS'],
                    ['name' => 'PACE', 'code' => 'PACE'],
                    ['name' => 'Lainnya', 'code' => null],
                ],
            ],
        ];

        foreach ($categories as $category) {
            $types = $category['types'];
            unset($category['types']);

            $category['created_at'] = now();
            $category['updated_at'] = now();

            $categoryId = DB::table('certification_categories')
                ->insertGetId($category);

            foreach ($types as $type) {
                DB::table('certification_types')->insertOrIgnore([
                    'category_id' => $categoryId,
                    'name' => $type['name'],
                    'code' => $type['code'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
