<?php

namespace App\Application\Services\Admin;

use App\Domain\Entities\RemedialTerm;
use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;
use Carbon\Carbon;

class ManageRemedialTermService
{
    public function __construct(
        private readonly RemedialTermRepositoryPort $termRepository,
    ) {}

    /** @return RemedialTerm[] */
    public function list(): array
    {
        return $this->termRepository->findAll();
    }

    public function findById(int $id): ?RemedialTerm
    {
        return $this->termRepository->findById($id);
    }

    public function create(array $data): RemedialTerm
    {
        if ($this->toBool($data['is_current_term'] ?? false)) {
            $this->termRepository->clearCurrentTermExcept(null);
        }

        return $this->termRepository->save($this->buildEntity(null, $data));
    }

    public function update(int $id, array $data): RemedialTerm
    {
        $existing = $this->requireById($id);

        if (array_key_exists('is_current_term', $data) && $this->toBool($data['is_current_term'])) {
            $this->termRepository->clearCurrentTermExcept($id);
        }

        return $this->termRepository->save($this->buildEntity($id, $data, $existing));
    }

    public function delete(int $id): void
    {
        $this->requireById($id);

        if ($this->termRepository->hasActiveRegistrations($id)) {
            throw new \DomainException('Không thể xóa đợt phụ đạo vì đã có đăng ký.');
        }

        $this->termRepository->softDelete($id);
    }

    private function requireById(int $id): RemedialTerm
    {
        $term = $this->termRepository->findById($id);

        if ($term === null) {
            throw new \DomainException('Đợt phụ đạo không tồn tại');
        }

        return $term;
    }

    private function buildEntity(?int $id, array $data, ?RemedialTerm $existing = null): RemedialTerm
    {
        return new RemedialTerm(
            id:                  $id,
            year:                (int) ($data['year'] ?? $existing?->year),
            semester:            (int) ($data['semester'] ?? $existing?->semester),
            name:                (string) ($data['name'] ?? $existing?->name),
            startDate:           $this->parseDate($data, 'start_date', $existing?->startDate),
            endDate:             $this->parseDate($data, 'end_date', $existing?->endDate),
            remedialCoefficient: (int) ($data['remedial_coefficient'] ?? $existing?->remedialCoefficient ?? 1),
            pricePerPeriod:      (int) ($data['price_per_period'] ?? $existing?->pricePerPeriod ?? 150000),
            priceCoefficient:    (float) ($data['price_coefficient'] ?? $existing?->priceCoefficient ?? 1),
            isCurrentTerm:       array_key_exists('is_current_term', $data)
                ? $this->toBool($data['is_current_term'])
                : ($existing?->isCurrentTerm ?? false),
            registrationStart:   $this->parseDate($data, 'registration_start', $existing?->registrationStart),
            registrationEnd:     $this->parseDate($data, 'registration_end', $existing?->registrationEnd),
        );
    }

    private function parseDate(array $data, string $key, ?Carbon $fallback): ?Carbon
    {
        if (! array_key_exists($key, $data)) {
            return $fallback;
        }

        if ($data[$key] === null) {
            return null;
        }

        $parsed = Carbon::parse($data[$key]);

        return match ($key) {
            'registration_start' => $parsed->copy()->startOfDay(),
            'registration_end'   => $parsed->copy()->endOfDay(),
            default              => $parsed,
        };
    }

    private function toBool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
