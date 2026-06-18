<?php

namespace App\Domain\Entities\TermStates;

use App\Domain\Enums\RemedialTermStatus;

class RegistrationOpenState extends BaseTermState
{
    public function getStatus(): RemedialTermStatus
    {
        return RemedialTermStatus::REGISTRATION_OPEN;
    }

    public function validateUpdate(array $data): void
    {
    }

    public function nextStatus(): ?RemedialTermStatus
    {
        return RemedialTermStatus::ACTIVE;
    }

    public function transitionTo(RemedialTermStatus $status): void
    {
        match ($status) {
            RemedialTermStatus::ACTIVE => $this->activate(),
            RemedialTermStatus::CANCELLED => $this->cancel(),
            default => throw new \DomainException("Không thể chuyển sang trạng thái {$status->description()} từ trạng thái: {$this->getStatus()->description()}"),
        };
    }

    public function activate(): void
    {
        // Allowed
    }

    public function cancel(): void
    {
        // Allowed
    }
}
