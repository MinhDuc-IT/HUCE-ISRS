<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\SystemConfig;

/**
 * Port (Interface) cho Repository quản lý cấu hình hệ thống.
 */
interface SystemConfigRepositoryPort
{
    /**
     * Lấy giá trị cấu hình theo key.
     */
    public function get(string $key, $default = null): ?string;

    /**
     * Lấy toàn bộ cấu hình.
     * @return SystemConfig[]
     */
    public function all(): array;

    /**
     * Lưu hoặc cập nhật cấu hình.
     */
    public function set(string $key, string $value, ?string $description = null): void;
}
