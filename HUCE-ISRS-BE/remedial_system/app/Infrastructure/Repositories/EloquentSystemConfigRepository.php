<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\SystemConfig;
use App\Domain\Repositories\SystemConfigRepositoryPort;
use App\Models\SystemConfig as EloquentSystemConfig;

class EloquentSystemConfigRepository implements SystemConfigRepositoryPort
{
    public function get(string $key, $default = null): ?string
    {
        $config = EloquentSystemConfig::where('Key', $key)->first();
        return $config ? $config->Value : $default;
    }

    public function all(): array
    {
        return EloquentSystemConfig::all()->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function set(string $key, string $value, ?string $description = null): void
    {
        EloquentSystemConfig::updateOrCreate(
            ['Key' => $key],
            ['Value' => $value, 'Description' => $description]
        );
    }

    private function toDomainEntity(EloquentSystemConfig $model): SystemConfig
    {
        return new SystemConfig(
            id:          $model->Id,
            key:         $model->Key,
            value:       $model->Value,
            description: $model->Description
        );
    }
}
