<?php

namespace App\Http\Controllers\Admin;

use App\Application\Services\Admin\AdminRemedialRegistrationQueryService;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Đăng ký phụ đạo', description: 'Tra cứu đăng ký phụ đạo – Admin')]
class RemedialRegistrationController extends BaseController
{
    public function __construct(
        private readonly AdminRemedialRegistrationQueryService $queryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->queryService->list(
            remedialTermId: $request->filled('remedial_term_id') ? $request->integer('remedial_term_id') : null,
            departmentId:   $request->filled('department_id') ? $request->integer('department_id') : null,
            subjectId:      $request->filled('subject_id') ? $request->integer('subject_id') : null,
            studentCode:    $request->query('student_code'),
        );

        return $this->success($items);
    }

    public function students(Request $request): JsonResponse
    {
        $request->validate([
            'remedial_term_id' => ['required', 'integer'],
            'subject_id'       => ['required', 'integer'],
        ]);

        try {
            $items = $this->queryService->listStudentsForGroup(
                $request->integer('remedial_term_id'),
                $request->integer('subject_id'),
            );

            return $this->success($items);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 404);
        }
    }
}
