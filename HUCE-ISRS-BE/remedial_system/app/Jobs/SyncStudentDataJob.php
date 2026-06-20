<?php

namespace App\Jobs;

use App\Application\Services\StudentSyncService;
use App\Domain\Ports\External\StudentInfoPort;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job bất đồng bộ: đồng bộ dữ liệu sinh viên & môn học từ University System.
 * Được dispatch sau khi User account đã được provision thành công.
 */
class SyncStudentDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Số lần retry nếu job thất bại.
     */
    public int $tries = 3;

    /**
     * Timeout tối đa cho mỗi lần chạy (giây).
     */
    public int $timeout = 60;

    public function __construct(
        private readonly string $studentCode,
    ) {}

    public function handle(StudentInfoPort $studentInfoPort, StudentSyncService $syncService): void
    {
        Log::info("[SyncStudentDataJob] Bắt đầu sync bất đồng bộ cho: {$this->studentCode}");

        try {
            $studentInfo = $studentInfoPort->getStudent($this->studentCode);
            $syncService->sync($this->studentCode, $studentInfo);

            Log::info("[SyncStudentDataJob] Sync thành công: {$this->studentCode}");
        } catch (\Throwable $e) {
            Log::error("[SyncStudentDataJob] Sync thất bại cho {$this->studentCode}: {$e->getMessage()}");
            throw $e; // Re-throw để queue retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[SyncStudentDataJob] Job thất bại hoàn toàn cho {$this->studentCode}: {$exception->getMessage()}");
    }
}
