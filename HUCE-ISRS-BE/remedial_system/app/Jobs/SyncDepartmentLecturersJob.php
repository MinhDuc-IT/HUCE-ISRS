<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Application\Services\TeacherSyncService;
use Illuminate\Support\Facades\Log;

class SyncDepartmentLecturersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $departmentId) {}

    public function handle(): void
    {
        try {
            $service = app(TeacherSyncService::class);
            $service->syncForDepartment($this->departmentId);
        } catch (\Exception $e) {
            Log::error('[SyncDepartmentLecturersJob] Error syncing department lecturers', ['departmentId' => $this->departmentId, 'error' => $e->getMessage()]);
            // Let the job fail/ retry according to queue config
            throw $e;
        }
    }
}
