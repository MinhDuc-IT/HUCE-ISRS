<?php

namespace App\Application\Services\Department;

use App\Domain\Enums\SystemConfigKey;
use App\Domain\Ports\Persistence\DepartmentRepositoryPort;
use App\Domain\Ports\Persistence\SystemConfigurationRepositoryPort;
use App\Infrastructure\Mail\DepartmentRemedialSummary;
use Illuminate\Support\Facades\Log;
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

        if (filter_var($dept->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \DomainException('Email của bộ môn không hợp lệ.');
        }

        $defaultSubject = $this->configRepository->get(
            SystemConfigKey::MAIL_SUMMARY_SUBJECT->value,
            'Danh sách sinh viên học phụ đạo'
        );
        $defaultBody = $this->configRepository->get(
            SystemConfigKey::MAIL_SUMMARY_BODY->value,
            'Gửi Bộ môn danh sách chi tiết các môn học và sinh viên đăng ký phụ đạo.'
        );

        $emailSubject = $subject ?: $defaultSubject;
        $emailBody = $body ?: $defaultBody;

        Log::info('[DepartmentSummaryEmail] Sending summary email.', [
            'department_id' => $departmentId,
            'to' => $dept->email,
            'mailer' => config('mail.default'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'from' => config('mail.from.address'),
        ]);

        Mail::mailer(config('mail.default'))
            ->to($dept->email)
            ->send(new DepartmentRemedialSummary(
                emailSubject: $emailSubject,
                emailBody: $emailBody,
            ));

        Log::info('[DepartmentSummaryEmail] Summary email sent.', [
            'department_id' => $departmentId,
            'to' => $dept->email,
        ]);
    }
}
