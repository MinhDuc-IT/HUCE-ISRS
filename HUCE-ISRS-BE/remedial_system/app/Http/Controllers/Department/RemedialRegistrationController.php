<?php

namespace App\Http\Controllers\Department;

use App\Application\Services\Department\DepartmentManageRegistrationService;
use App\Application\Services\Department\DepartmentRegistrationQueryService;
use App\Application\Services\StudentRegistrationPresenter;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Department\UpdateRegistrationLecturerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RemedialRegistrationController extends BaseController
{
    public function __construct(
        private readonly DepartmentRegistrationQueryService $queryService,
        private readonly DepartmentManageRegistrationService $manageService,
        private readonly StudentRegistrationPresenter $presenter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $items = $this->queryService->list(
                $request->user(),
                $request->filled('remedial_term_id') ? $request->integer('remedial_term_id') : null,
                $request->query('student_code'),
            );

            return $this->success($items);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    public function update(UpdateRegistrationLecturerRequest $request, int $id): JsonResponse
    {
        try {
            $registration = $this->manageService->updateLecturer(
                $request->user(),
                $id,
                $request->validated()
            );

            return $this->success(
                $this->presenter->format($registration),
                'Cập nhật thông tin giảng viên thành công'
            );
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'Không tìm thấy') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        }
    }
}
