<?php

namespace App\Domain\Entities;

use App\Domain\States\TutoringRequest\RequestState;
use App\Domain\States\TutoringRequest\PendingState;
use App\Domain\Enums\TutoringRequestStatus;
use Carbon\Carbon;

/**
 * Domain Entity – Đơn xin mở lớp phụ đạo
 */
class TutoringRequest
{
    private RequestState $state;

    public function __construct(
        public readonly ?int    $id,
        public readonly int     $studentId,
        public readonly int     $courseId,
        public readonly int     $tutoringTermId,
        public readonly ?int    $requestedPeriods = null,
        public TutoringRequestStatus|int $status = TutoringRequestStatus::PENDING,
        public ?string          $note = null,
        public readonly Carbon  $createdAt = new Carbon(),
    ) {
        if (is_int($status)) {
            $status = TutoringRequestStatus::from($status);
        }
        $this->status = $status;
        
        // Khởi tạo state dựa trên status hiện tại
        $this->state = $this->resolveState($this->status);
    }

    public function transitionTo(RequestState $state): void
    {
        $this->state = $state;
        $this->status = $this->resolveStatusFromState($state);
    }

    public function approve(): void
    {
        $this->state->approve();
    }

    public function reject(string $reason): void
    {
        $this->state->reject($reason);
        $this->note = $reason;
    }

    public function pay(): void
    {
        $this->state->pay();
    }

    private function resolveState(TutoringRequestStatus $status): RequestState
    {
        return match ($status) {
            TutoringRequestStatus::APPROVED => new \App\Domain\States\TutoringRequest\ApprovedState($this),
            TutoringRequestStatus::REJECTED => new \App\Domain\States\TutoringRequest\RejectedState($this, $this->note ?? ''),
            default                         => new PendingState($this),
        };
    }

    private function resolveStatusFromState(RequestState $state): TutoringRequestStatus
    {
        return match (get_class($state)) {
            \App\Domain\States\TutoringRequest\ApprovedState::class => TutoringRequestStatus::APPROVED,
            \App\Domain\States\TutoringRequest\RejectedState::class => TutoringRequestStatus::REJECTED,
            default                                                 => TutoringRequestStatus::PENDING,
        };
    }
}
