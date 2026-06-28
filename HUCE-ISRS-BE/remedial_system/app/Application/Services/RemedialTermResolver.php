<?php

namespace App\Application\Services;

use App\Domain\Entities\RemedialTerm;
use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;

class RemedialTermResolver
{
    public function __construct(
        private readonly RemedialTermRepositoryPort $termRepository,
    ) {}

    public function requireCurrentTermId(?int $override = null): int
    {
        if ($override !== null) {
            return $override;
        }

        $term = $this->termRepository->findCurrent();

        if ($term === null || $term->id === null) {
            throw new \DomainException('Hiện không có đợt phụ đạo nào đang mở.');
        }

        return $term->id;
    }

    public function requireCurrentTerm(?int $override = null): RemedialTerm
    {
        $termId = $this->requireCurrentTermId($override);

        return $this->termRepository->findById($termId)
            ?? throw new \DomainException('Hiện không có đợt phụ đạo nào đang mở.');
    }
}
