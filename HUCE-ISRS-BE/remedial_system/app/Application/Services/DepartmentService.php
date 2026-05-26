<?php

namespace App\Application\Services;

use App\Application\Services\Department\SendDepartmentSummaryEmailService;
use App\Domain\Ports\Persistence\DepartmentRepositoryPort;

/**
 * Legacy facade — dùng cho các caller cũ; logic email chuyển sang SendDepartmentSummaryEmailService.
 */
class DepartmentService
{
    public function __construct(
        private readonly DepartmentRepositoryPort $departmentRepository,
        private readonly SendDepartmentSummaryEmailService $summaryEmailService,
    ) {}

    public function getAllDepartments(): array
    {
        return $this->departmentRepository->findAll();
    }

    public function getDepartmentDetail(int $id): ?\App\Domain\Entities\Department
    {
        return $this->departmentRepository->findById($id);
    }

    public function sendSummaryEmail(int $id, ?string $subject = null, ?string $body = null): void
    {
        $this->summaryEmailService->send($id, $subject, $body);
    }
}
