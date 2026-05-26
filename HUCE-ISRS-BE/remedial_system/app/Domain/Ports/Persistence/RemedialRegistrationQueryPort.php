<?php

namespace App\Domain\Ports\Persistence;

interface RemedialRegistrationQueryPort
{
    /**
     * Danh sách đăng ký phụ đạo cho admin (có filter).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listForAdmin(
        ?int $remedialTermId = null,
        ?int $departmentId = null,
        ?int $subjectId = null,
        ?string $studentCode = null,
    ): array;

    /**
     * Môn học có đăng ký phụ đạo (group theo subject) thuộc bộ môn.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listSubjectAssignmentSummaries(
        int $departmentId,
        ?int $remedialTermId = null,
    ): array;
}
