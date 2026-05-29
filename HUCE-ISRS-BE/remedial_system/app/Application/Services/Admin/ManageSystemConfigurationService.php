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

    public function create(string $key, string $value, ?string $description = null): SystemConfiguration
    {
        $existing = $this->configRepository->findByKey($key);
        if ($existing !== null) {
            throw new \DomainException('Cấu hình đã tồn tại.');
        }

        $this->configRepository->set($key, $value, $description);

        $created = $this->configRepository->findByKey($key);
        if ($created === null) {
            throw new \RuntimeException('Không thể tạo cấu hình hệ thống.');
        }

        return $created;
    }

    public function update(string $key, string $value, ?string $description = null): SystemConfiguration
    {
        $existing = $this->configRepository->findByKey($key);
        if ($existing === null) {
            throw new \DomainException('Cấu hình không tồn tại.');
        }

        $nextDescription = $description ?? $existing->description;
        $this->configRepository->set($key, $value, $nextDescription);

        $updated = $this->configRepository->findByKey($key);
        if ($updated === null) {
            throw new \RuntimeException('Không thể cập nhật cấu hình hệ thống.');
        }

        return $updated;
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

    public function delete(string $key): void
    {
        $existing = $this->configRepository->findByKey($key);
        if ($existing === null) {
            throw new \DomainException('Cấu hình không tồn tại.');
        }

        $this->configRepository->delete($key);
    }
}
