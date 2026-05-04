<?php

namespace App\Domain\Entities;

/**
 * Domain Entity – Cấu hình hệ thống (Dạng Key-Value)
 */
class SystemConfig
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $key,
        public string           $value,
        public readonly ?string $description = null,
    ) {}
}
