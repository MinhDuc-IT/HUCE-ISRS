<?php

namespace App\Application\Services\Department;

use App\Domain\Ports\Persistence\RemedialRegistrationQueryPort;
use App\Models\User;

class DepartmentRegistrationQueryService
{
    public function __construct(
        private readonly RemedialRegistrationQueryPort $queryPort,
        private readonly DepartmentProfileService $profileService,
    ) {}

    public function list(User $user, ?int $remedialTermId = null, ?string $studentCode = null): array
    {
        $departmentId = $this->profileService->resolveDepartmentId($user);

        return $this->queryPort->listForAdmin(
            remedialTermId: $remedialTermId,
            departmentId:   $departmentId,
            subjectId:      null,
            studentCode:    $studentCode,
        );
    }
}
