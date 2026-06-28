<?php

namespace App\Application\Services\Department;

use App\Application\Services\RemedialTermResolver;
use App\Domain\Ports\Persistence\RemedialRegistrationQueryPort;
use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;
use App\Models\RemedialRegistration as RemedialRegistrationModel;
use App\Models\User;

class DepartmentSubjectAssignmentQueryService
{
    public function __construct(
        private readonly RemedialRegistrationQueryPort $queryPort,
        private readonly RemedialTermRepositoryPort $termRepository,
        private readonly DepartmentProfileService $profileService,
        private readonly RemedialTermResolver $termResolver,
    ) {}

    public function list(User $user, ?int $remedialTermId = null): array
    {
        $departmentId = $this->profileService->resolveDepartmentId($user);
        $termId = $this->termResolver->requireCurrentTermId($remedialTermId);

        $rows = $this->queryPort->listSubjectAssignmentSummaries($departmentId, $termId);

        return array_map(function (array $row) use ($departmentId, $termId) {
            $row['can_assign_lecturer'] = $this->canAssignLecturerForSubject(
                (int) $row['subject_id'],
                $departmentId,
                $termId,
            );

            return $row;
        }, $rows);
    }

    private function canAssignLecturerForSubject(
        int $subjectId,
        int $departmentId,
        int $remedialTermId,
    ): bool {
        $hasRegistration = RemedialRegistrationModel::query()
            ->where('subject_id', $subjectId)
            ->where('remedial_term_id', $remedialTermId)
            ->where('is_deleted', false)
            ->whereHas('subject', fn ($q) => $q
                ->where('department_id', $departmentId)
                ->where('is_deleted', false))
            ->exists();

        if (! $hasRegistration) {
            return false;
        }

        $term = $this->termRepository->findById($remedialTermId);

        if ($term === null) {
            return false;
        }

        return ! $term->isRegistrationOpen();
    }
}
