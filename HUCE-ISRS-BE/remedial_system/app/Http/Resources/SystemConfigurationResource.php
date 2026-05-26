<?php

namespace App\Http\Resources;

use App\Domain\Entities\SystemConfiguration;
use Illuminate\Http\Request;

class SystemConfigurationResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $config = $this->resource;

        if ($config instanceof SystemConfiguration) {
            return [
                'key'         => $config->key,
                'value'       => $config->value,
                'description' => $config->description,
            ];
        }

        return parent::toArray($request);
    }
}
