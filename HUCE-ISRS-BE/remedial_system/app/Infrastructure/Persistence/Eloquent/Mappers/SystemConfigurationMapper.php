<?php

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\SystemConfiguration;
use App\Models\SystemConfiguration as SystemConfigurationModel;

final class SystemConfigurationMapper
{
    public static function toDomain(SystemConfigurationModel $model): SystemConfiguration
    {
        return new SystemConfiguration(
            id:          $model->id,
            key:         $model->key,
            value:       $model->value,
            description: $model->description,
        );
    }
}
