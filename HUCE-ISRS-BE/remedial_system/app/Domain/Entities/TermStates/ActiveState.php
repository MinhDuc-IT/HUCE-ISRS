<?php

namespace App\Domain\Entities\TermStates;

use App\Domain\Enums\RemedialTermStatus;

class ActiveState extends BaseTermState
{
    public function getStatus(): RemedialTermStatus
    {
        return RemedialTermStatus::ACTIVE;
    }

    public function validateUpdate(array $data): void
    {
    }

    public function nextStatus(): ?RemedialTermStatus
    {
        return RemedialTermStatus::COMPLETED;
    }

    public function transitionTo(RemedialTermStatus $status): void
    {
        match ($status) {
            RemedialTermStatus::COMPLETED => $this->complete(),
            RemedialTermStatus::CANCELLED => $this->cancel(),
            default => throw new \DomainException("Không thể chuyển sang trạng thái {$status->description()} từ trạng thái: {$this->getStatus()->description()}"),
        };
    }

    public function complete(): void
    {
        // Allowed
    }

    public function cancel(): void
    {
        // Allowed
    }
}
