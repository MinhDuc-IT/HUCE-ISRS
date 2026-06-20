<?php

namespace App\Domain\Entities\TermStates;

use App\Domain\Entities\RemedialTerm;

abstract class BaseTermState implements TermState
{
    public function __construct(
        protected RemedialTerm $term,
    ) {}

    public function nextStatus(): ?\App\Domain\Enums\RemedialTermStatus
    {
        return null;
    }

    public function transitionTo(\App\Domain\Enums\RemedialTermStatus $status): void
    {
        throw new \DomainException("Không thể chuyển sang trạng thái {$status->description()} từ trạng thái: {$this->getStatus()->description()}");
    }

    public function openRegistration(): void
    {
        throw new \DomainException("Không thể mở đăng ký từ trạng thái: {$this->getStatus()->description()}");
    }

    public function activate(): void
    {
        throw new \DomainException("Không thể bắt đầu đợt từ trạng thái: {$this->getStatus()->description()}");
    }

    public function complete(): void
    {
        throw new \DomainException("Không thể hoàn thành đợt từ trạng thái: {$this->getStatus()->description()}");
    }

    public function cancel(): void
    {
        throw new \DomainException("Không thể huỷ đợt từ trạng thái: {$this->getStatus()->description()}");
    }
}
