<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kirim reminder dokumen setiap hari pukul 07:00 WIB (00:00 UTC)
Schedule::command('reminders:send')->dailyAt('00:00');
