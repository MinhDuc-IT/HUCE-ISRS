<?php

namespace App\Domain\Entities\TermStates;

use App\Domain\Enums\RemedialTermStatus;

class CompletedState extends BaseTermState
{
    public function getStatus(): RemedialTermStatus
    {
        return RemedialTermStatus::COMPLETED;
    }

    public function validateUpdate(array $data): void
    {
        throw new \DomainException('Không thể cập nhật thông tin đợt phụ đạo đã hoàn thành.');
    }

    public function transitionTo(\App\Domain\Enums\RemedialTermStatus $status): void
    {
        parent::transitionTo($status);
    }
}
