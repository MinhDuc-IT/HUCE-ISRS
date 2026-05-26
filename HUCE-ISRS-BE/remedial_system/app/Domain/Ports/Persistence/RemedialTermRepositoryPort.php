<?php

namespace App\Domain\Ports\Persistence;

use App\Domain\Entities\RemedialTerm;

interface RemedialTermRepositoryPort
{
    public function findById(int $id): ?RemedialTerm;

    public function findCurrent(): ?RemedialTerm;

    /** @return RemedialTerm[] */
    public function findAll(): array;

    public function save(RemedialTerm $term): RemedialTerm;

    public function softDelete(int $id): void;

    /** Bỏ cờ đợt hiện tại trên mọi bản ghi, trừ $exceptId nếu có. */
    public function clearCurrentTermExcept(?int $exceptId = null): void;

    public function hasActiveRegistrations(int $id): bool;
}
