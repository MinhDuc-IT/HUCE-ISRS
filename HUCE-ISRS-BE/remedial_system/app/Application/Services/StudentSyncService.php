<?php

namespace App\Application\Services;

use App\Domain\Entities\StudentInfo;
use App\Domain\Ports\StudentInfoPort;
use App\Domain\Repositories\StudentRepositoryPort;
use App\Domain\Repositories\CourseRepositoryPort;
use Illuminate\Support\Facades\Log;

class StudentSyncService
{
    public function __construct(
        private readonly StudentInfoPort $studentInfoPort,
        private readonly StudentRepositoryPort $studentRepository,
        private readonly CourseRepositoryPort $courseRepository,
    ) {}

    /**
     * Đồng bộ thông tin sinh viên và các môn học liên quan.
     */
    public function sync(string $studentCode, StudentInfo $studentInfo): void
    {
        // 1. Đồng bộ bảng Student
        $this->studentRepository->updateOrCreate($studentCode, [
            'FullName' => $studentInfo->fullName,
            'Email'    => $studentInfo->universityEmail ?? $studentInfo->personalEmail,
            'UpdatedAt'=> now(),
        ]);

        // 2. Đồng bộ danh sách môn học
        $courses = $this->studentInfoPort->getCourses($studentCode);
        foreach ($courses as $courseInfo) {
            if (empty($courseInfo->courseCode)) {
                Log::warning("[StudentSync] Bỏ qua môn học không có mã học phần cho {$studentCode}");
                continue;
            }

            // Đảm bảo có khoa (Department)
            $dept = $this->courseRepository->firstOrCreateDepartment('DEFAULT', [
                'Name' => 'Khoa Mặc Định'
            ]);

            $this->courseRepository->updateOrCreateCourse($courseInfo->courseCode, [
                'CourseName'   => $courseInfo->subjectName,
                'Credits'      => $courseInfo->credits,
                'DepartmentId' => $dept->Id,
            ]);
        }
    }
}
