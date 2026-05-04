<?php

namespace App\Domain\Entities;

/**
 * Domain Entity – Cấu hình hệ thống
 */
class SystemConfig
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?int    $minStudentsPerClass = null,
        public readonly ?int    $maxStudentsPerClass = null,
        public readonly ?int    $defaultPeriods = null,
    ) {}
}
