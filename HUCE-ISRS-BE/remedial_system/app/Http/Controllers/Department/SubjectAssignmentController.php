<?php

namespace App\Http\Controllers\Department;

use App\Application\Services\Department\DepartmentManageRegistrationService;
use App\Application\Services\Department\DepartmentSubjectAssignmentQueryService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Department\UpdateRegistrationLecturerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Bộ môn – Phân công GV theo môn', description: 'Danh sách môn có đăng ký phụ đạo và gán GV hàng loạt')]
class SubjectAssignmentController extends BaseController
{
    public function __construct(
        private readonly DepartmentSubjectAssignmentQueryService $queryService,
        private readonly DepartmentManageRegistrationService $manageService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $items = $this->queryService->list(
                $request->user(),
                $request->filled('remedial_term_id') ? $request->integer('remedial_term_id') : null,
            );

            return $this->success($items, 'Danh sách môn có đăng ký phụ đạo');
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    public function update(UpdateRegistrationLecturerRequest $request, int $subjectId): JsonResponse
    {
        try {
            $count = $this->manageService->updateLecturerForSubject(
                $request->user(),
                $subjectId,
                $request->validated()
            );

            return $this->success(
                ['updated_count' => $count, 'subject_id' => $subjectId],
                "Đã gán giảng viên cho {$count} đăng ký phụ đạo của môn học."
            );
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'Không có') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        }
    }
}
