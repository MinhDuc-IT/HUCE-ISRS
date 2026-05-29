<?php

namespace App\Http\Controllers\Admin;

use App\Application\Services\Admin\ManageRemedialTermService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\StoreRemedialTermRequest;
use App\Http\Requests\Admin\UpdateRemedialTermRequest;
use App\Http\Resources\RemedialTermResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Đợt phụ đạo', description: 'Quản lý đợt phụ đạo (Admin)')]
class RemedialTermController extends BaseController
{
    public function __construct(
        private readonly ManageRemedialTermService $termService,
    ) {}

    public function index(): JsonResponse
    {
        $terms = $this->termService->list();

        return $this->success(
            RemedialTermResource::collection($terms)->resolve()
        );
    }

    public function store(StoreRemedialTermRequest $request): JsonResponse
    {
        try {
            $term = $this->termService->create($request->validated());

            return $this->success(
                (new RemedialTermResource($term))->resolve(),
                'Tạo đợt phụ đạo thành công',
                201
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $term = $this->termService->findById($id);

        if ($term === null) {
            return $this->error('Đợt phụ đạo không tồn tại', null, 404);
        }

        return $this->success((new RemedialTermResource($term))->resolve());
    }

    public function update(UpdateRemedialTermRequest $request, int $id): JsonResponse
    {
        try {
            $term = $this->termService->update($id, $request->validated());

            return $this->success(
                (new RemedialTermResource($term))->resolve(),
                'Cập nhật đợt phụ đạo thành công'
            );
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'không tồn tại') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->termService->delete($id);

            return $this->success(null, 'Xóa đợt phụ đạo thành công');
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'không tồn tại') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        }
    }

    public function updateStatus(\App\Http\Requests\Admin\ChangeRemedialTermStatusRequest $request, int $id): JsonResponse
    {
        try {
            $status = \App\Domain\Enums\RemedialTermStatus::from((int) $request->input('status'));
            $term = $this->termService->transitionTo($id, $status);

            return $this->success(
                (new RemedialTermResource($term))->resolve(),
                'Cập nhật trạng thái đợt phụ đạo thành công'
            );
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'không tồn tại') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        }
    }
}
