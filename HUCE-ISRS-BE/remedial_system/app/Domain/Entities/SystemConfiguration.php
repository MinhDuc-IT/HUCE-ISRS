<?php

namespace App\Domain\Entities;

/** Domain entity – cấu hình hệ thống ({@see system_configurations}). */
class SystemConfiguration
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $key,
        public string           $value,
        public readonly ?string $description = null,
    ) {}
}
