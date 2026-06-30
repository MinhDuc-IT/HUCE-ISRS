<?php

namespace App\Application\Services\Admin;

use App\Domain\Ports\External\StudentInfoPort;
use App\Domain\Ports\Persistence\RemedialRegistrationQueryPort;
use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;
use Illuminate\Support\Facades\Log;

class AdminRemedialRegistrationQueryService
{
    public function __construct(
        private readonly RemedialRegistrationQueryPort $queryPort,
        private readonly RemedialTermRepositoryPort $termRepository,
        private readonly SubjectRepositoryPort $subjectRepository,
        private readonly StudentInfoPort $studentInfoPort,
    ) {}

    public function list(
        ?int $remedialTermId = null,
        ?int $departmentId = null,
        ?int $subjectId = null,
        ?string $studentCode = null,
    ): array {
        return $this->queryPort->listGroupedForAdmin(
            remedialTermId: $remedialTermId,
            departmentId:   $departmentId,
            subjectId:      $subjectId,
            studentCode:    $studentCode,
        );
    }

    public function listStudentsForGroup(int $remedialTermId, int $subjectId): array
    {
        $term = $this->termRepository->findById($remedialTermId);
        if ($term === null) {
            throw new \DomainException('Không tìm thấy đợt phụ đạo.');
        }

        $subject = $this->subjectRepository->findById($subjectId);
        if ($subject === null) {
            throw new \DomainException('Không tìm thấy môn học.');
        }

        $students = $this->queryPort->listStudentsForAdminGroup($remedialTermId, $subjectId);

        return array_map(function (array $row) use ($term, $subject) {
            $row['class_name'] = $this->resolveClassName(
                $row['student_code'] ?? '',
                $subject->subjectCode,
                $term->year,
                $term->semester,
            );

            return $row;
        }, $students);
    }

    private function resolveClassName(
        string $studentCode,
        string $subjectCode,
        int $year,
        int $semester,
    ): ?string {
        if ($studentCode === '') {
            return null;
        }

        try {
            $courses = $this->studentInfoPort->getRegisteredCoursesForSemester(
                $studentCode,
                $year,
                $semester,
            );

            foreach ($courses as $course) {
                if (strcasecmp($course->subjectCode, $subjectCode) === 0) {
                    $className = trim($course->plannedClass);

                    return $className !== '' ? $className : null;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[AdminRemedialRegistrationQueryService] Không lấy được lớp sinh viên', [
                'student_code' => $studentCode,
                'subject_code' => $subjectCode,
                'error'        => $e->getMessage(),
            ]);
        }

        return null;
    }
}
