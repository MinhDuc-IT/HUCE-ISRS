<?php

namespace App\Console\Commands;

use App\Domain\Enums\RemedialTermStatus;
use App\Models\RemedialTerm;
use App\Infrastructure\Persistence\Eloquent\Mappers\RemedialTermMapper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateRemedialTermStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remedial-term:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật tự động trạng thái đợt phụ đạo dựa trên ngày tháng (Registration -> Active -> Completed)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        $updatedCount = 0;

        // 1. Chuyển từ REGISTRATION_OPEN sang ACTIVE nếu đã hết hạn đăng ký
        $termsToActivate = RemedialTerm::where('status', RemedialTermStatus::REGISTRATION_OPEN->value)
            ->whereNotNull('registration_end')
            ->where('registration_end', '<', $now)
            ->get();

        foreach ($termsToActivate as $term) {
            $domainTerm = RemedialTermMapper::toDomain($term);
            $domainTerm = $domainTerm->transitionTo($domainTerm->nextStatus() ?? RemedialTermStatus::ACTIVE);
            $term->status = $domainTerm->status->value;
            $term->save();
            $updatedCount++;
            $this->info("Đã chuyển đợt phụ đạo ID {$term->id} sang trạng thái ACTIVE (Hết hạn đăng ký).");
            Log::info("Auto update term status: Term ID {$term->id} moved to ACTIVE.");
        }

        // 2. Chuyển từ ACTIVE sang COMPLETED nếu đã hết hạn học
        $termsToComplete = RemedialTerm::where('status', RemedialTermStatus::ACTIVE->value)
            ->whereNotNull('end_date')
            ->where('end_date', '<', $now)
            ->get();

        foreach ($termsToComplete as $term) {
            $domainTerm = RemedialTermMapper::toDomain($term);
            $domainTerm = $domainTerm->transitionTo($domainTerm->nextStatus() ?? RemedialTermStatus::COMPLETED);
            $term->status = $domainTerm->status->value;
            $term->save();
            $updatedCount++;
            $this->info("Đã chuyển đợt phụ đạo ID {$term->id} sang trạng thái COMPLETED (Hết hạn đợt học).");
            Log::info("Auto update term status: Term ID {$term->id} moved to COMPLETED.");
        }

        if ($updatedCount === 0) {
            $this->info('Không có đợt phụ đạo nào cần cập nhật trạng thái.');
        } else {
            $this->info("Đã cập nhật tổng cộng {$updatedCount} đợt phụ đạo.");
        }
    }
}
