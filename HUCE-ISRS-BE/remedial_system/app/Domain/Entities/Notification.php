<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Thông báo
 */
class Notification
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?int    $userId,
        public readonly ?string $title,
        public readonly ?string $content,
        public bool             $isRead = false,
        public readonly Carbon  $createdAt = new Carbon(),
    ) {}
}
