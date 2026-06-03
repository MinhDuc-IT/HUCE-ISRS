<?php

namespace App\Application\Services;

use App\Domain\Entities\StudentInfo;
use App\Domain\Ports\External\StudentInfoPort;
use App\Domain\Ports\Persistence\StudentRepositoryPort;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;
use Illuminate\Support\Facades\Log;

class StudentSyncService
{
    public function __construct(
        private readonly StudentInfoPort $studentInfoPort,
        private readonly StudentRepositoryPort $studentRepository,
        private readonly SubjectRepositoryPort $subjectRepository,
    ) {}

    public function sync(string $studentCode, StudentInfo $studentInfo): void
    {
        $this->studentRepository->updateOrCreate($studentCode, [
            'full_name'  => $studentInfo->fullName,
            'email'      => $studentInfo->universityEmail ?? $studentInfo->personalEmail,
            'is_deleted' => false,
        ]);

        $courses = $this->studentInfoPort->getCourses($studentCode);
        foreach ($courses as $courseInfo) {
            $subjectCode = $courseInfo->code();
            if ($subjectCode === '') {
                Log::warning("[StudentSync] Skip course without subject code for {$studentCode}");
                continue;
            }

            $existing = $this->subjectRepository->findByCode($subjectCode);

            $payload = [
                'name'       => $courseInfo->subjectName,
                'credits'    => $courseInfo->credits,
                'is_deleted' => false,
            ];

            if ($existing === null) {
                $deptCode = $courseInfo->departmentCode !== null
                    ? (string) $courseInfo->departmentCode
                    : 'DEFAULT';

                $dept = $this->subjectRepository->firstOrCreateDepartment($deptCode, [
                    'name' => $courseInfo->departmentName ?: 'Khoa Mac Dinh',
                ]);

                $payload['department_id'] = $dept->id;
            }

            $this->subjectRepository->updateOrCreateSubject($subjectCode, $payload);
        }
    }
}
