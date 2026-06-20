<?php

namespace App\Domain\Entities\TermStates;

use App\Domain\Enums\RemedialTermStatus;

class CancelledState extends BaseTermState
{
    public function getStatus(): RemedialTermStatus
    {
        return RemedialTermStatus::CANCELLED;
    }

    public function validateUpdate(array $data): void
    {
        throw new \DomainException('Không thể cập nhật thông tin đợt phụ đạo đã huỷ.');
    }
}
