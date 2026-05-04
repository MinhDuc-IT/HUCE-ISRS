<?php

namespace App\Domain\Entities;

/**
 * Domain Entity – Chi tiết thanh toán
 */
class PaymentDetail
{
    public function __construct(
        public readonly ?int    $id,
        public readonly int     $paymentId,
        public readonly int     $tutoringClassId,
        public readonly ?float  $hours = null,
        public readonly ?float  $amount = null,
    ) {}
}
