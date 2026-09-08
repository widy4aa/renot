<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            CertificationCategorySeeder::class,
            ReminderScheduleSeeder::class,
            UserSeeder::class,
            DocumentSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
