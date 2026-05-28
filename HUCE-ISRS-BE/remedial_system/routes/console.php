<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Chạy job cập nhật trạng thái đợt phụ đạo hàng giờ (hoặc hàng ngày tùy nhu cầu, ở đây ví dụ chạy mỗi giờ)
Schedule::command('remedial-term:update-status')->hourly();
