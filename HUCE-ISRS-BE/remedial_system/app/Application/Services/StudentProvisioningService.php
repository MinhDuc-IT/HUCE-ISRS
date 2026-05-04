<?php

namespace App\Application\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Domain\Entities\StudentInfo;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Domain\Ports\StudentInfoPort;

/**
 * StudentProvisioningService – Auto-provision tài khoản sinh viên (Option B).
 *
 * Khi sinh viên đăng nhập lần đầu bằng student_code:
 *   1. Tìm trong bảng users local → nếu có, bỏ qua.
 *   2. Nếu chưa có → gọi University System xác minh student_code tồn tại.
 *   3. Tạo user local với:
 *        - role          = sinh_vien
 *        - student_code  = student_code
 *        - email         = universityEmail ?? personalEmail
 *        - password      = student_code (mật khẩu mặc định lần đầu)
 *   4. Trả về User model đã được tạo/tìm thấy.
 *
 * Đây là Application Service – không chứa business logic domain,
 * chỉ điều phối giữa Infrastructure (university API) và local DB.
 */
class StudentProvisioningService
{
    public function __construct(
        private readonly StudentInfoPort $studentInfoPort,
    ) {}

    /**
     * Tìm hoặc tạo tài khoản local cho sinh viên.
     *
     * @param  string $studentCode Mã sinh viên (VD: SV001)
     * @return User                User model đã tồn tại hoặc vừa được tạo
     *
     * @throws StudentNotFoundException Sinh viên không tồn tại trên University System
     * @throws ExternalSystemException  Không thể kết nối University System
     */
    public function findOrProvision(string $studentCode): User
    {
        // ── Bước 1: Tìm trong local DB trước ─────────────────────────────────
        $existingUser = User::where('student_code', $studentCode)
            ->where('role', User::ROLE_SINH_VIEN)
            ->first();

        if ($existingUser !== null) {
            Log::debug("[StudentProvisioning] Tìm thấy tài khoản local: {$studentCode}");
            return $existingUser;
        }

        // ── Bước 2: Gọi University System xác minh student_code ──────────────
        Log::info("[StudentProvisioning] Chưa có tài khoản local cho {$studentCode}, đang xác minh qua University System...");

        $studentInfo = $this->studentInfoPort->getStudent($studentCode);
        // Nếu sinh viên không tồn tại, getStudent() sẽ throw StudentNotFoundException

        // ── Bước 3: Tạo/Cập nhật thông tin sinh viên vào bảng Student ──────────
        $this->syncStudentToLocalDB($studentCode, $studentInfo);

        // ── Bước 4: Tạo tài khoản local (User) ───────────────────────────────
        $user = $this->createLocalUser($studentCode, $studentInfo);

        Log::info("[StudentProvisioning] Đã đồng bộ sinh viên và tạo user: {$studentCode}", [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        return $user;
    }

    /**
     * Đồng bộ thông tin sinh viên vào DB nội bộ.
     */
    private function syncStudentToLocalDB(string $studentCode, StudentInfo $studentInfo): void
    {
        Student::updateOrCreate(
            ['StudentCode' => $studentCode],
            [
                'FullName' => $studentInfo->fullName,
                'Email'    => $studentInfo->universityEmail ?? $studentInfo->personalEmail,
                'UpdatedAt'=> now(),
            ]
        );

        // Đồng bộ danh sách môn học của sinh viên
        $courses = $this->studentInfoPort->getCourses($studentCode);
        foreach ($courses as $courseInfo) {
            // Nếu không có mã học phần từ hệ thống trường, bỏ qua để tránh lỗi DB
            if (empty($courseInfo->courseCode)) {
                Log::warning("[StudentProvisioning] Bỏ qua môn học không có mã học phần cho {$studentCode}");
                continue;
            }

            // Giả sử môn học chưa có khoa, mặc định gán DepartmentId = 1
            $dept = Department::firstOrCreate(
                ['DepartmentCode' => 'DEFAULT'],
                ['Name' => 'Khoa Mặc Định']
            );

            Course::updateOrCreate(
                ['CourseCode' => $courseInfo->courseCode],
                [
                    'CourseName'   => $courseInfo->subjectName,
                    'Credits'      => $courseInfo->credits,
                    'DepartmentId' => $dept->Id,
                ]
            );
        }
    }

    /**
     * Kiểm tra sinh viên có đăng nhập lần đầu không
     * (chưa đổi mật khẩu từ mật khẩu mặc định = student_code).
     *
     * @param User   $user        User model
     * @param string $studentCode Mã sinh viên
     */
    public function isFirstLogin(User $user, string $studentCode): bool
    {
        return Hash::check($studentCode, $user->password);
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function createLocalUser(string $studentCode, StudentInfo $studentInfo): User
    {
        // Email ưu tiên: email trường → email cá nhân → email giả từ mã SV
        $email = $studentInfo->universityEmail
            ?? $studentInfo->personalEmail
            ?? "{$studentCode}@student.remedial.edu.vn";

        // Nếu email đã bị dùng bởi user khác (edge case), dùng email giả
        if (User::where('email', $email)->exists()) {
            $email = "{$studentCode}@student.remedial.edu.vn";
        }

        return User::create([
            'name'         => $studentInfo->fullName,
            'email'        => $email,
            'password'     => Hash::make($studentCode),   // mật khẩu mặc định = student_code
            'role'         => User::ROLE_SINH_VIEN,
            'student_code' => $studentCode,
            'department_id'=> null,
        ]);
    }
}
