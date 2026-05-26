<?php

namespace App\Application\Services\Department;

use App\Domain\Enums\SystemConfigKey;
use App\Domain\Ports\Persistence\DepartmentRepositoryPort;
use App\Domain\Ports\Persistence\SystemConfigurationRepositoryPort;
use App\Infrastructure\Mail\DepartmentRemedialSummary;
use App\Models\Department as DepartmentModel;
use App\Models\RemedialRegistration as RemedialRegistrationModel;
use Illuminate\Support\Facades\Mail;

class SendDepartmentSummaryEmailService
{
    public function __construct(
        private readonly DepartmentRepositoryPort $departmentRepository,
        private readonly SystemConfigurationRepositoryPort $configRepository,
    ) {}

    public function send(int $departmentId, ?string $subject = null, ?string $body = null): void
    {
        $dept = $this->departmentRepository->findById($departmentId);

        if ($dept === null) {
            throw new \DomainException('Bộ môn không tồn tại');
        }

        if (! $dept->email) {
            throw new \DomainException('Bộ môn chưa được cấu hình địa chỉ Email.');
        }

        $defaultSubject = $this->configRepository->get(
            SystemConfigKey::MAIL_SUMMARY_SUBJECT->value,
            'Danh sách sinh viên học phụ đạo'
        );
        $defaultBody = $this->configRepository->get(
            SystemConfigKey::MAIL_SUMMARY_BODY->value,
            'Gửi Bộ môn danh sách chi tiết các môn học và sinh viên đăng ký phụ đạo.'
        );

        $registrations = RemedialRegistrationModel::query()
            ->whereHas('subject', fn ($q) => $q->where('department_id', $departmentId))
            ->with(['subject', 'user'])
            ->orderBy('subject_id')
            ->get();

        if ($registrations->isEmpty()) {
            throw new \DomainException('Hiện không có đăng ký phụ đạo nào thuộc bộ môn này.');
        }

        $deptModel = DepartmentModel::find($departmentId);

        Mail::to($dept->email)->send(new DepartmentRemedialSummary(
            department: $deptModel,
            registrations: $registrations,
            emailSubject: $subject ?: $defaultSubject,
            emailBody: $body ?: $defaultBody,
        ));
    }
}
