<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReminderScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            ['days_before' => 60, 'urgency' => 'rendah'],
            ['days_before' => 45, 'urgency' => 'rendah'],
            ['days_before' => 30, 'urgency' => 'sedang'],
            ['days_before' => 20, 'urgency' => 'sedang'],
            ['days_before' => 10, 'urgency' => 'tinggi'],
            ['days_before' => 7, 'urgency' => 'tinggi'],
            ['days_before' => 6, 'urgency' => 'tinggi'],
            ['days_before' => 5, 'urgency' => 'tinggi'],
            ['days_before' => 4, 'urgency' => 'tinggi'],
            ['days_before' => 3, 'urgency' => 'sangat_tinggi'],
            ['days_before' => 2, 'urgency' => 'sangat_tinggi'],
            ['days_before' => 1, 'urgency' => 'sangat_tinggi'],
        ];

        foreach ($schedules as $schedule) {
            DB::table('reminder_schedules')->insertOrIgnore([
                'days_before' => $schedule['days_before'],
                'urgency' => $schedule['urgency'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
