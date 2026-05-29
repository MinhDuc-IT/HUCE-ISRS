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
        if (array_key_exists('remedial_coefficient', $data) || array_key_exists('price_per_period', $data) || array_key_exists('price_coefficient', $data)) {
            throw new \DomainException('Không thể cập nhật hệ số hoặc đơn giá khi đang mở đăng ký.');
        }
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
