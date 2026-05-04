<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\TutoringTerm;

/**
 * Port (Interface) cho Repository quản lý đợt phụ đạo.
 */
interface TutoringTermRepositoryPort
{
    public function findById(int $id): ?TutoringTerm;

    public function findDefault(): ?TutoringTerm;

    /**
     * @return TutoringTerm[]
     */
    public function findAll(): array;
}
