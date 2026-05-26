<?php

namespace App\Application\Services\Admin;

use App\Domain\Ports\Persistence\RemedialRegistrationQueryPort;

class AdminRemedialRegistrationQueryService
{
    public function __construct(
        private readonly RemedialRegistrationQueryPort $queryPort,
    ) {}

    public function list(
        ?int $remedialTermId = null,
        ?int $departmentId = null,
        ?int $subjectId = null,
        ?string $studentCode = null,
    ): array {
        return $this->queryPort->listForAdmin(
            remedialTermId: $remedialTermId,
            departmentId:   $departmentId,
            subjectId:      $subjectId,
            studentCode:    $studentCode,
        );
    }
}
