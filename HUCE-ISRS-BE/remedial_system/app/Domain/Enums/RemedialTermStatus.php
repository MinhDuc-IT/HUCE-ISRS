<?php

namespace App\Domain\Enums;

enum RemedialTermStatus: int
{
    case DRAFT = 0;
    case REGISTRATION_OPEN = 1;
    case ACTIVE = 2;
    case COMPLETED = 3;
    case CANCELLED = 4;

    public function description(): string
    {
        return match ($this) {
            self::DRAFT => 'Nháp',
            self::REGISTRATION_OPEN => 'Đang mở đăng ký',
            self::ACTIVE => 'Đang diễn ra (hoạt động)',
            self::COMPLETED => 'Đã hoàn thành',
            self::CANCELLED => 'Đã hủy',
        };
    }

    public function isCurrent(): bool
    {
        return in_array($this, [self::REGISTRATION_OPEN, self::ACTIVE], true);
    }

    public function canTransitionAutomatically(): bool
    {
        return in_array($this, [self::REGISTRATION_OPEN, self::ACTIVE], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'DRAFT',
            self::REGISTRATION_OPEN => 'REGISTRATION_OPEN',
            self::ACTIVE => 'ACTIVE',
            self::COMPLETED => 'COMPLETED',
            self::CANCELLED => 'CANCELLED',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match ($label) {
            'DRAFT' => self::DRAFT,
            'REGISTRATION_OPEN' => self::REGISTRATION_OPEN,
            'ACTIVE' => self::ACTIVE,
            'COMPLETED' => self::COMPLETED,
            'CANCELLED' => self::CANCELLED,
            default => throw new \ValueError("Unknown RemedialTermStatus label: {$label}"),
        };
    }
}
