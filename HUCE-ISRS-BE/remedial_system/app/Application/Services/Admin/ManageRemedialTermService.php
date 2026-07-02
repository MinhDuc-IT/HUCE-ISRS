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
//         if ($this->toBool($data['is_current_term'] ?? false)) {
//             $this->termRepository->clearCurrentTermExcept(null);
//         }

        return $this->termRepository->save($this->buildEntity(null, $data));
    }

    public function update(int $id, array $data): RemedialTerm
    {
        $existing = $this->requireById($id);

//         if (array_key_exists('is_current_term', $data) && $this->toBool($data['is_current_term'])) {
//             $this->termRepository->clearCurrentTermExcept($id);
//         }
        $existing->validateUpdate($data);
        $this->assertUpdateFieldsAllowed($existing->status, $data);

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

    public function transitionTo(int $id, \App\Domain\Enums\RemedialTermStatus $status): RemedialTerm
    {
        $term = $this->requireById($id);

        if ($term->status === $status) {
            return $term;
        }

        if (in_array($status, [
            \App\Domain\Enums\RemedialTermStatus::REGISTRATION_OPEN,
            \App\Domain\Enums\RemedialTermStatus::ACTIVE,
        ], true)) {
            $this->assertNoOtherOpenTerm($id);
        }

        return $this->termRepository->save($term->transitionTo($status));
    }

    private function buildEntity(?int $id, array $data, ?RemedialTerm $existing = null): RemedialTerm
    {
        $status = $existing?->status ?? \App\Domain\Enums\RemedialTermStatus::DRAFT;
        $allowPriceUpdate = in_array($status, [
            \App\Domain\Enums\RemedialTermStatus::DRAFT,
            \App\Domain\Enums\RemedialTermStatus::REGISTRATION_OPEN,
        ], true);
        $allowRegDateUpdate = $status !== \App\Domain\Enums\RemedialTermStatus::ACTIVE && $status !== \App\Domain\Enums\RemedialTermStatus::COMPLETED && $status !== \App\Domain\Enums\RemedialTermStatus::CANCELLED;

        return new RemedialTerm(
            id:                  $id,
            year:                (int) ($data['year'] ?? $existing?->year),
            semester:            (int) ($data['semester'] ?? $existing?->semester),
            name:                (string) ($data['name'] ?? $existing?->name),
            startDate:           $this->parseDate($data, 'start_date', $existing?->startDate),
            endDate:             $this->parseDate($data, 'end_date', $existing?->endDate),
            remedialCoefficient: $allowPriceUpdate ? (int) ($data['remedial_coefficient'] ?? $existing?->remedialCoefficient ?? 1) : ($existing?->remedialCoefficient ?? 1),
            pricePerPeriod:      $allowPriceUpdate ? (int) ($data['price_per_period'] ?? $existing?->pricePerPeriod ?? 150000) : ($existing?->pricePerPeriod ?? 150000),
            priceCoefficient:    $allowPriceUpdate ? (float) ($data['price_coefficient'] ?? $existing?->priceCoefficient ?? 1) : ($existing?->priceCoefficient ?? 1),
//             isCurrentTerm:       array_key_exists('is_current_term', $data)
//                 ? $this->toBool($data['is_current_term'])
//                 : ($existing?->isCurrentTerm ?? false),
            isCurrentTerm:       $status->isCurrent(),
            registrationStart:   $allowRegDateUpdate ? $this->parseDate($data, 'registration_start', $existing?->registrationStart) : $existing?->registrationStart,
            registrationEnd:     $allowRegDateUpdate ? $this->parseDate($data, 'registration_end', $existing?->registrationEnd) : $existing?->registrationEnd,
            status:              $existing?->status ?? \App\Domain\Enums\RemedialTermStatus::DRAFT,
        );
    }

    private function assertUpdateFieldsAllowed(\App\Domain\Enums\RemedialTermStatus $status, array $data): void
    {
        $forbiddenFields = match ($status) {
            \App\Domain\Enums\RemedialTermStatus::DRAFT,
            \App\Domain\Enums\RemedialTermStatus::REGISTRATION_OPEN => [],
            \App\Domain\Enums\RemedialTermStatus::ACTIVE => [
                'remedial_coefficient',
                'price_per_period',
                'price_coefficient',
                'registration_start',
                'registration_end',
            ],
            \App\Domain\Enums\RemedialTermStatus::COMPLETED => array_keys($data),
            \App\Domain\Enums\RemedialTermStatus::CANCELLED => array_keys($data),
        };

        $disallowed = array_values(array_intersect(array_keys($data), $forbiddenFields));

        if ($disallowed !== []) {
            $labels = [
                'remedial_coefficient' => 'Hệ số PD',
                'price_per_period' => 'Đơn giá 1 tiết',
                'price_coefficient' => 'Hệ số đơn giá',
                'registration_start' => 'Ngày bắt đầu đăng ký',
                'registration_end' => 'Ngày kết thúc đăng ký',
            ];

            $friendlyFields = array_map(
                fn (string $field) => $labels[$field] ?? $field,
                $disallowed
            );

            throw new \DomainException(
                'Trạng thái hiện tại không cho phép cập nhật: ' . implode(', ', $friendlyFields)
            );
        }
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

//      private function toBool(mixed $value): bool
//     {
//         return filter_var($value, FILTER_VALIDATE_BOOLEAN);
//     }

    private function assertNoOtherOpenTerm(int $excludeId): void
    {
        $hasOpenTerm = \App\Models\RemedialTerm::whereIn('status', [
            \App\Domain\Enums\RemedialTermStatus::REGISTRATION_OPEN->value,
            \App\Domain\Enums\RemedialTermStatus::ACTIVE->value
        ])->where('id', '!=', $excludeId)->exists();

        if ($hasOpenTerm) {
            throw new \DomainException('Đã có một đợt phụ đạo khác đang mở đăng ký hoặc đang hoạt động. Vui lòng hoàn thành hoặc huỷ đợt đó trước.');
        }
    }
}
