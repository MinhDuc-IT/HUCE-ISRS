<?php

namespace App\Application\Services;

use App\Models\User;
use App\Domain\Entities\StudentInfo;
use App\Domain\Ports\External\StudentInfoPort;
use App\Domain\Ports\Persistence\UserRepositoryPort;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Domain\Exceptions\ExternalSystemException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * StudentProvisioningService – Điều phối quy trình Auto-provision.
 */
class StudentProvisioningService
{
    public function __construct(
        private readonly StudentInfoPort $studentInfoPort,
        private readonly UserRepositoryPort $userRepository,
        private readonly StudentSyncService $syncService,
    ) {}

    /**
     * Tìm hoặc tạo tài khoản local cho sinh viên.
     */
    public function findOrProvision(string $studentCode): User
    {
        // ── Bước 1: Tìm trong local DB (Repository) ──────────────────────────
        $existingUser = $this->userRepository->findByStudentCode($studentCode);

        if ($existingUser !== null) {
            Log::debug("[StudentProvisioning] Tìm thấy tài khoản local: {$studentCode}");
            return $existingUser;
        }

        // ── Bước 2: Gọi University System xác minh ────────────────────────────
        Log::info("[StudentProvisioning] Đang xác minh {$studentCode} qua University System...");
        $studentInfo = $this->studentInfoPort->getStudent($studentCode);

        // ── Bước 3 & 4: Thực hiện Sync và Provision trong một Transaction ─────
        return DB::transaction(function () use ($studentCode, $studentInfo) {
            // Đồng bộ dữ liệu Student/Course
            $this->syncService->sync($studentCode, $studentInfo);

            // Tạo User account
            $user = $this->provisionUser($studentCode, $studentInfo);

            Log::info("[StudentProvisioning] Đã đồng bộ và provision thành công: {$studentCode}");

            return $user;
        });
    }

    /**
     * Kiểm tra sinh viên có đăng nhập lần đầu không.
     */
    public function isFirstLogin(User $user, string $studentCode): bool
    {
        return Hash::check($studentCode, $user->password);
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function provisionUser(string $studentCode, StudentInfo $studentInfo): User
    {
        $email = $this->resolveProvisioningEmail($studentCode, $studentInfo);

        return $this->userRepository->create([
            'name'         => $studentInfo->fullName,
            'email'        => $email,
            'password'     => Hash::make($studentCode),
            'role'         => User::ROLE_SINH_VIEN,
            'student_code' => $studentCode,
            'department_id'=> null,
        ]);
    }

    private function resolveProvisioningEmail(string $studentCode, StudentInfo $studentInfo): string
    {
        $email = $studentInfo->universityEmail
            ?? $studentInfo->personalEmail
            ?? "{$studentCode}@student.remedial.edu.vn";

        if ($this->userRepository->findByEmail($email)) {
            return "{$studentCode}@student.remedial.edu.vn";
        }

        return $email;
    }
}
