<?php

namespace App\Domain\Enums;

enum PaymentStatus: string
{
    case PENDING   = 'pending';
    case PAID      = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'Chờ thanh toán',
            self::PAID      => 'Đã thanh toán',
            self::CANCELLED => 'Đã hủy',
        };
    }
}
