<?php

namespace App\Domain\States\TutoringRequest;

class ApprovedState extends RequestState
{
    public function approve(): void
    {
        // Đã approved rồi, không làm gì cả
    }

    public function reject(string $reason): void
    {
        throw new \DomainException("Không thể từ chối đơn đã được duyệt.");
    }

    public function pay(): void
    {
        $this->context->transitionTo(new PaidState($this->context));
    }
}
