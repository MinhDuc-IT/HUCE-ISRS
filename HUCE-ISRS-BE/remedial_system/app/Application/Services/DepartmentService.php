<?php

namespace App\Application\Services;

use App\Domain\Entities\Department;
use App\Domain\Repositories\DepartmentRepositoryPort;
use App\Domain\Repositories\TutoringClassRepositoryPort;
use App\Domain\Repositories\SystemConfigRepositoryPort;
use App\Domain\Enums\SystemConfigKey;
use Illuminate\Support\Facades\Mail;
use App\Mail\DepartmentRemedialSummary;
use App\Models\TutoringClass as EloquentTutoringClass;

class DepartmentService
{
    public function __construct(
        private readonly DepartmentRepositoryPort    $departmentRepository,
        private readonly TutoringClassRepositoryPort $classRepository,
        private readonly SystemConfigRepositoryPort  $configRepository
    ) {}

    public function getAllDepartments(): array
    {
        return $this->departmentRepository->findAll();
    }

    public function getDepartmentDetail(int $id): ?Department
    {
        return $this->departmentRepository->findById($id);
    }

    /**
     * Gửi email danh sách sinh viên về bộ môn.
     * Sử dụng template cấu hình từ SystemConfig.
     */
    public function sendSummaryEmail(int $id, ?string $subject = null, ?string $body = null): void
    {
        $dept = $this->departmentRepository->findById($id);
        if (!$dept) throw new \DomainException('Bộ môn không tồn tại');
        if (!$dept->email) throw new \DomainException('Bộ môn chưa được cấu hình địa chỉ Email.');

        // Lấy cấu hình template mặc định nếu không được truyền vào
        $defaultSubject = $this->configRepository->get(SystemConfigKey::MAIL_SUMMARY_SUBJECT->value, 'Danh sách sinh viên học phụ đạo');
        $defaultBody    = $this->configRepository->get(SystemConfigKey::MAIL_SUMMARY_BODY->value, 'Gửi Bộ môn danh sách chi tiết các môn học và sinh viên đăng ký phụ đạo.');

        $finalSubject = $subject ?: $defaultSubject;
        $finalBody    = $body ?: $defaultBody;

        // Lấy danh sách lớp phụ đạo thuộc bộ môn
        // Tạm thời sử dụng Eloquent Query để lấy data phức tạp (Course + Teacher + Enrollments)
        // Trong Clean Architecture chuẩn, Repository nên gánh việc này và trả về Aggregate Root hoặc DTO.
        $tutoringClasses = EloquentTutoringClass::whereHas('course', function($q) use ($id) {
                $q->where('DepartmentId', $id);
            })
            ->with(['course', 'teacher', 'enrollments.student'])
            ->get();

        if ($tutoringClasses->isEmpty()) {
            throw new \DomainException('Hiện không có môn học phụ đạo nào thuộc bộ môn này.');
        }

        // Gửi Mail
        // Lưu ý: DepartmentRemedialSummary mong đợi Eloquent models cho Department và TutoringClasses.
        // Để giữ tính tương thích với Mailable hiện tại, ta cần convert Department Entity về Model hoặc update Mailable.
        // Tạm thời ta lấy lại model của Department.
        $deptModel = \App\Models\Department::find($id);

        Mail::to($dept->email)->send(new DepartmentRemedialSummary(
            department: $deptModel,
            tutoringClasses: $tutoringClasses,
            emailSubject: $finalSubject,
            emailBody: $finalBody
        ));
    }
}
