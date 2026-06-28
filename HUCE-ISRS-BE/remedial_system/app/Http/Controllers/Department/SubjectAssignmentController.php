<?php

namespace App\Http\Controllers\Department;

use App\Application\Services\Department\DepartmentManageRegistrationService;
use App\Application\Services\Department\DepartmentSubjectAssignmentQueryService;
use App\Application\Services\Department\DepartmentProfileService;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Department\UpdateRegistrationLecturerRequest;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Bộ môn – Phân công GV theo môn', description: 'Danh sách môn có đăng ký phụ đạo và gán GV hàng loạt')]
class SubjectAssignmentController extends BaseController
{
    public function __construct(
        private readonly DepartmentSubjectAssignmentQueryService $queryService,
        private readonly DepartmentManageRegistrationService $manageService,
        private readonly SubjectRepositoryPort $subjectRepository,
        private readonly DepartmentProfileService $profileService,
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
                $request->validated(),
                $request->filled('remedial_term_id') ? $request->integer('remedial_term_id') : null,
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

    public function getTeachers(Request $request, int $subjectId): JsonResponse
    {
        try {
            $user = $request->user();
            $departmentId = $this->profileService->resolveDepartmentId($user);

            // Kiểm tra môn học thuộc bộ môn
            $subject = $this->subjectRepository->findById($subjectId);
            if ($subject === null || $subject->departmentId !== $departmentId) {
                return $this->error('Môn học không thuộc bộ môn của bạn.', null, 403);
            }

            // Lấy danh sách giáo viên của bộ môn
            $teachers = Teacher::where('department_id', $departmentId)
                ->select('id', 'first_name', 'last_name', 'email', 'phone')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn ($teacher) => [
                    'id' => $teacher->id,
                    'name' => trim($teacher->last_name . ' ' . $teacher->first_name),
                    'email' => $teacher->email,
                    'phone' => $teacher->phone,
                    'display' => trim($teacher->last_name . ' ' . $teacher->first_name) . ' - ' . $teacher->email,
                ])
                ->values();

            return $this->success($teachers, 'Danh sách giáo viên của bộ môn');
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }
}
