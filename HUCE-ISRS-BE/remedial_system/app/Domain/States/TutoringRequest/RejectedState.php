<?php

namespace App\Domain\States\TutoringRequest;

class RejectedState extends RequestState
{
    public function __construct(
        \App\Domain\Entities\TutoringRequest $context,
        public readonly string $reason
    ) {
        parent::__construct($context);
    }

    public function approve(): void { throw new \DomainException("Không thể duyệt đơn đã bị từ chối."); }
    public function reject(string $reason): void {}
    public function pay(): void { throw new \DomainException("Không thể thanh toán đơn đã bị từ chối."); }
}
