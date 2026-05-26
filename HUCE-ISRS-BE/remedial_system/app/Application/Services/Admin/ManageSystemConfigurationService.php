<?php

namespace App\Application\Services\Admin;

use App\Domain\Entities\SystemConfiguration;
use App\Domain\Ports\Persistence\SystemConfigurationRepositoryPort;

class ManageSystemConfigurationService
{
    public function __construct(
        private readonly SystemConfigurationRepositoryPort $configRepository,
    ) {}

    /** @return SystemConfiguration[] */
    public function list(): array
    {
        return $this->configRepository->all();
    }

    /**
     * @param  array<int, array{key: string, value?: string}>  $settings
     * @return SystemConfiguration[]
     */
    public function updateMany(array $settings): array
    {
        foreach ($settings as $item) {
            $this->configRepository->set($item['key'], $item['value'] ?? '');
        }

        return $this->configRepository->all();
    }
}
