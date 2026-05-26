<?php

namespace App\Application\Services\Department;

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
    ) {}

    public function list(User $user, ?int $remedialTermId = null): array
    {
        $departmentId = $this->profileService->resolveDepartmentId($user);

        $rows = $this->queryPort->listSubjectAssignmentSummaries($departmentId, $remedialTermId);

        return array_map(function (array $row) use ($departmentId, $remedialTermId) {
            $row['can_assign_lecturer'] = $this->canAssignLecturerForSubject(
                (int) $row['subject_id'],
                $departmentId,
                $remedialTermId,
            );

            return $row;
        }, $rows);
    }

    private function canAssignLecturerForSubject(
        int $subjectId,
        int $departmentId,
        ?int $remedialTermId,
    ): bool {
        $query = RemedialRegistrationModel::query()
            ->where('subject_id', $subjectId)
            ->whereHas('subject', fn ($q) => $q
                ->where('department_id', $departmentId)
                ->where('is_deleted', false));

        if ($remedialTermId !== null) {
            $query->where('remedial_term_id', $remedialTermId);
        }

        $termIds = $query->distinct()->pluck('remedial_term_id');

        if ($termIds->isEmpty()) {
            return false;
        }

        foreach ($termIds as $termId) {
            $term = $this->termRepository->findById((int) $termId);

            if ($term === null) {
                continue;
            }

            if ($term->isRegistrationOpen()) {
                return false;
            }
        }

        return true;
    }
}
