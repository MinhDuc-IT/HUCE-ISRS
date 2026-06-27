<?php

namespace App\Application\Services;

use App\Domain\Ports\External\StudentInfoPort;
use App\Domain\Ports\Persistence\TeacherRepositoryPort;
use Illuminate\Support\Facades\Log;

class TeacherSyncService
{
    public function __construct(
        private readonly StudentInfoPort $studentInfoPort,
        private readonly TeacherRepositoryPort $teacherRepository,
    ) {}

    public function syncForDepartment(int $departmentId): void
    {
        try {
            $lecturers = $this->studentInfoPort->getLecturersForDepartment($departmentId);

            foreach ($lecturers as $item) {
                $email = $item['email'] ?? null;
                if (empty($email)) {
                    continue;
                }

                $this->teacherRepository->updateOrCreateByEmail($email, [
                    'department_id' => $item['departmentId'] ?? $departmentId,
                    'first_name'    => $item['firstName'] ?? null,
                    'last_name'     => $item['lastName'] ?? null,
                    'phone'         => $item['phone'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[TeacherSyncService] Lỗi khi đồng bộ giảng viên', ['error' => $e->getMessage()]);
        }
    }
}
