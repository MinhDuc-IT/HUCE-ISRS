<?php

namespace App\Application\Services;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Course;
use App\Models\TutoringTerm;
use App\Domain\Entities\TutoringRequest;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Domain\Ports\StudentInfoPort;
use App\Domain\Repositories\TutoringRequestRepositoryPort;
use App\Domain\Enums\TutoringRequestStatus;

/**
 * RemedialRegistrationService – Use case đăng ký học phần bổ sung.
 *
 * Đây là Application Service (Use Case) trong DDD.
 * Điều phối Domain Entities, Ports và Repositories.
 * KHÔNG chứa business logic phức tạp – business logic nằm trong Domain Entities.
 */
class RemedialRegistrationService
{
    public function __construct(
        private readonly StudentInfoPort            $studentInfoPort,
        private readonly TutoringRequestRepositoryPort $requestRepository,
    ) {}

    /**
     * Use case: Đăng ký học phần bổ sung.
     *
     * Luồng:
     * 1. Lấy thông tin sinh viên từ University System (qua Port)
     * 2. Kiểm tra sinh viên có tín chỉ nợ không
     * 3. Lấy danh sách môn học, kiểm tra môn được yêu cầu có bị rớt không
     * 4. Kiểm tra chưa đăng ký trùng
     * 5. Tạo Registration entity và lưu
     *
     * @param string $studentCode Mã sinh viên
     * @param string $courseCode  Mã học phần muốn đăng ký bổ sung
     * @return TutoringRequest    Đơn đăng ký đã được tạo
     *
     * @throws StudentNotFoundException  Sinh viên không tồn tại
     * @throws ExternalSystemException   Lỗi kết nối University System
     * @throws \DomainException          Vi phạm business rules
     */
    public function register(string $studentCode, string $courseCode): TutoringRequest
    {
        // ─── Bước 1: Lấy thông tin sinh viên ────────────────────────────────
        $localStudent = Student::where('StudentCode', $studentCode)->first();
        if (! $localStudent) {
            throw new StudentNotFoundException("Sinh viên {$studentCode} không tồn tại trong hệ thống.");
        }

        // Lấy thông tin từ University System để xác thực điều kiện
        $studentInfo = $this->studentInfoPort->getStudent($studentCode);

        // ─── Bước 2: Kiểm tra điều kiện ─────────────────────────────────────
        if (! $studentInfo->isEligibleForRemedial()) {
            throw new \DomainException(
                "Sinh viên {$studentCode} không có môn nợ, không đủ điều kiện đăng ký bổ sung."
            );
        }

        // ─── Bước 3: Xác minh môn học ──────────────────────────────────────
        $courses = $this->studentInfoPort->getCourses($studentCode);
        $targetCourseInfo = collect($courses)->first(fn($c) => $c->courseCode === $courseCode);

        if (!$targetCourseInfo) {
            throw new \DomainException("Học phần {$courseCode} không thuộc chương trình học của sinh viên.");
        }

        if (!$targetCourseInfo->isEligibleForRemedial()) {
            throw new \DomainException("Học phần {$courseCode} đã đạt, không thể đăng ký phụ đạo.");
        }

        $localCourse = Course::where('CourseCode', $courseCode)->first();
        if (!$localCourse) {
             throw new \DomainException("Dữ liệu môn học {$courseCode} chưa được đồng bộ.");
        }

        // Lấy đợt phụ đạo mặc định
        $currentTerm = TutoringTerm::where('IsDefault', true)->first();
        if (!$currentTerm) {
             throw new \DomainException("Hệ thống hiện không có đợt phụ đạo nào đang mở.");
        }

        // ─── Bước 4: Kiểm tra đăng ký trùng ─────────────────────────────────
        if ($this->requestRepository->existsActiveRequest($localStudent->Id, $localCourse->Id, $currentTerm->Id)) {
            throw new \DomainException("Bạn đã đăng ký môn {$courseCode} trong đợt này rồi.");
        }

        // ─── Bước 5: Tạo và lưu ─────────────────────────────────────────────
        $tutoringRequest = new TutoringRequest(
            id:               null,
            studentId:        $localStudent->Id,
            courseId:         $localCourse->Id,
            tutoringTermId:   $currentTerm->Id,
            requestedPeriods: null,
            status:           TutoringRequestStatus::PENDING,
            createdAt:        Carbon::now(),
        );

        return $this->requestRepository->save($tutoringRequest);
    }

    /**
     * Use case: Đăng ký nhiều môn cùng lúc.
     */
    public function bulkRegister(string $studentCode, array $courseCodes): array
    {
        $results = [];
        foreach ($courseCodes as $code) {
            try {
                $results[] = $this->register($studentCode, $code);
            } catch (\Exception $e) {
                // Có thể ném lỗi hoặc bỏ qua môn lỗi tùy yêu cầu, 
                // Ở đây ta ném lỗi để transaction roll back (nếu có)
                throw $e;
            }
        }
        return $this->getRegistrations($studentCode);
    }

    /**
     * Use case: Lấy danh sách đăng ký của sinh viên.
     *
     * @param string $studentCode Mã sinh viên
     * @return TutoringRequest[]
     */
    public function getRegistrations(string $studentCode): array
    {
        $localStudent = Student::where('StudentCode', $studentCode)->first();
        if (! $localStudent) return [];

        return $this->requestRepository->findByStudent($localStudent->Id);
    }

    /**
     * Use case: Lấy danh sách môn đủ điều kiện đăng ký bổ sung của sinh viên.
     *
     * @param string $studentId Mã sinh viên
     * @return array            Mảng CourseResult đủ điều kiện
     */
    public function getEligibleCourses(string $studentId): array
    {
        $this->studentInfoPort->getStudent($studentId);

        $courses = $this->studentInfoPort->getCourses($studentId);

        return array_values(array_filter(
            $courses,
            fn($course) => $course->isEligibleForRemedial()
        ));
    }

    /**
     * Use case: Hủy đăng ký.
     *
     * @param int    $requestId    ID đơn đăng ký
     * @param string $studentCode  Mã sinh viên (để xác minh ownership)
     *
     * @throws \DomainException Nếu không tìm thấy hoặc không thuộc sinh viên này
     */
    public function cancelRegistration(int $requestId, string $studentCode): void
    {
        $request = $this->requestRepository->findById($requestId);
        
        $localStudent = Student::where('StudentCode', $studentCode)->first();

        if ($request === null || ! $localStudent || $request->studentId !== $localStudent->Id) {
            throw new \DomainException("Không tìm thấy đơn đăng ký #{$requestId}.");
        }

        // Entity không có hàm cancel(), cần tự đổi trạng thái
        if (in_array($request->status, [TutoringRequestStatus::APPROVED, TutoringRequestStatus::REJECTED])) {
            throw new \DomainException('Không thể hủy đơn đã được xử lý.');
        }

        $request->status = TutoringRequestStatus::CANCELLED;
        $this->requestRepository->update($request);
    }
}
