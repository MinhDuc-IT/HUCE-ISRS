<?php

namespace App\Domain\States\TutoringRequest;

class PendingState extends RequestState
{
    public function approve(): void
    {
        // Chuyển sang ApprovedState
        $this->context->transitionTo(new ApprovedState($this->context));
    }

    public function reject(string $reason): void
    {
        // Chuyển sang RejectedState
        $this->context->transitionTo(new RejectedState($this->context, $reason));
    }

    public function pay(): void
    {
        throw new \DomainException("Không thể thanh toán đơn đang chờ duyệt.");
    }
}
