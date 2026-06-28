<?php

namespace App\Application\Services\Department;

use App\Application\Services\RemedialTermResolver;
use App\Domain\Ports\Persistence\RemedialRegistrationQueryPort;
use App\Models\User;

class DepartmentRegistrationQueryService
{
    public function __construct(
        private readonly RemedialRegistrationQueryPort $queryPort,
        private readonly DepartmentProfileService $profileService,
        private readonly RemedialTermResolver $termResolver,
    ) {}

    public function list(User $user, ?int $remedialTermId = null, ?string $studentCode = null): array
    {
        $departmentId = $this->profileService->resolveDepartmentId($user);
        $termId = $this->termResolver->requireCurrentTermId($remedialTermId);

        return $this->queryPort->listForAdmin(
            remedialTermId: $termId,
            departmentId:   $departmentId,
            subjectId:      null,
            studentCode:    $studentCode,
        );
    }
}
