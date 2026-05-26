<?php

namespace App\Domain\Ports\Persistence;

use App\Domain\Entities\SystemConfiguration;

interface SystemConfigurationRepositoryPort
{
    public function get(string $key, mixed $default = null): ?string;

    /** @return SystemConfiguration[] */
    public function all(): array;

    public function set(string $key, string $value, ?string $description = null): void;
}
