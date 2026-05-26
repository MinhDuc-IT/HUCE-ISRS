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
                Log::warning("[StudentSync] Bỏ qua môn học không có mã học phần cho {$studentCode}");
                continue;
            }

            $existing = $this->subjectRepository->findByCode($subjectCode);

            $payload = [
                'name'       => $courseInfo->subjectName,
                'credits'    => $courseInfo->credits,
                'is_deleted' => false,
            ];

            // Chỉ gán bộ môn khi tạo mới; không ghi đè nếu admin đã gán department_id.
            if ($existing === null) {
                $dept = $this->subjectRepository->firstOrCreateDepartment('DEFAULT', [
                    'name' => 'Khoa Mặc Định',
                ]);
                $payload['department_id'] = $dept->id;
            }

            $this->subjectRepository->updateOrCreateSubject($subjectCode, $payload);
        }
    }
}
