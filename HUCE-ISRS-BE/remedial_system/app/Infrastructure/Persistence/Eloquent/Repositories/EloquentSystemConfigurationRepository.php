<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Ports\Persistence\SystemConfigurationRepositoryPort;
use App\Infrastructure\Persistence\Eloquent\Mappers\SystemConfigurationMapper;
use App\Models\SystemConfiguration as SystemConfigurationModel;

class EloquentSystemConfigurationRepository implements SystemConfigurationRepositoryPort
{
    public function get(string $key, mixed $default = null): ?string
    {
        $config = SystemConfigurationModel::where('key', $key)
            ->where('is_deleted', false)
            ->first();

        return $config ? $config->value : ($default !== null ? (string) $default : null);
    }

    public function all(): array
    {
        return SystemConfigurationModel::where('is_deleted', false)
            ->get()
            ->map(fn ($model) => SystemConfigurationMapper::toDomain($model))
            ->all();
    }

    public function set(string $key, string $value, ?string $description = null): void
    {
        SystemConfigurationModel::updateOrCreate(
            ['key' => $key],
            [
                'value'       => $value,
                'description' => $description,
                'is_deleted'  => false,
            ]
        );
    }
}
