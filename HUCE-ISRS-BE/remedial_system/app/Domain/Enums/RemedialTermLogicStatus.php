<?php

namespace App\Domain\Enums;

enum RemedialTermLogicStatus: int
{
    case DRAFT = 0;
    case ACTIVE_PENDING_REGISTRATION = 10;
    case REGISTRATION_OPEN = 11;
    case ACTIVE_PENDING_CLASS = 12;
    case ACTIVE_IN_PROGRESS = 13;
    case ACTIVE_ENDED = 14;
    case COMPLETED = 30;
    case CANCELLED = 40;

    public function description(): string
    {
        return match ($this) {
            self::DRAFT => 'Nháp',
            self::ACTIVE_PENDING_REGISTRATION => 'Sắp mở đăng ký',
            self::REGISTRATION_OPEN => 'Đang mở đăng ký',
            self::ACTIVE_PENDING_CLASS => 'Đã đóng đăng ký',
            self::ACTIVE_IN_PROGRESS => 'Đang học',
            self::ACTIVE_ENDED => 'Chờ hoàn thành',
            self::COMPLETED => 'Đã hoàn thành',
            self::CANCELLED => 'Đã hủy',
        };
    }
}
