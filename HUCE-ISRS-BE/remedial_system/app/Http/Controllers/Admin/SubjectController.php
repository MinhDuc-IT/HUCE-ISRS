<?php

namespace App\Http\Controllers\Admin;

use App\Application\Services\Admin\ManageSubjectService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Môn học', description: 'Quản lý danh mục môn học – chỉ Admin')]
class SubjectController extends BaseController
{
    public function __construct(
        private readonly ManageSubjectService $subjectService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(
            SubjectResource::collection($this->subjectService->list())->resolve()
        );
    }

    public function show(int $id): JsonResponse
    {
        $subject = $this->subjectService->findById($id);

        if ($subject === null) {
            return $this->error('Môn học không tồn tại', null, 404);
        }

        return $this->success((new SubjectResource($subject))->resolve());
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        try {
            $subject = $this->subjectService->create($request->validated());

            return $this->success(
                (new SubjectResource($subject))->resolve(),
                'Thêm môn học thành công',
                201
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage());
        }
    }

    public function update(UpdateSubjectRequest $request, int $id): JsonResponse
    {
        try {
            $subject = $this->subjectService->update($id, $request->validated());

            return $this->success(
                (new SubjectResource($subject))->resolve(),
                'Cập nhật môn học thành công'
            );
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'không tồn tại') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->subjectService->delete($id);

            return $this->success(null, 'Xóa môn học thành công');
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 404);
        }
    }
}
