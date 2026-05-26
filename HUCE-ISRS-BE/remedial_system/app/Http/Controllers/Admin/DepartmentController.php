<?php

namespace App\Http\Controllers\Admin;

use App\Application\Services\Admin\ManageDepartmentService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\SendDepartmentEmailRequest;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Bộ môn', description: 'Quản lý thông tin Bộ môn và liên lạc')]
class DepartmentController extends BaseController
{
    public function __construct(
        private readonly ManageDepartmentService $departmentService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(
            DepartmentResource::collection($this->departmentService->list())->resolve()
        );
    }

    public function show(int $id): JsonResponse
    {
        $dept = $this->departmentService->findById($id);

        if ($dept === null) {
            return $this->error('Bộ môn không tồn tại', null, 404);
        }

        return $this->success((new DepartmentResource($dept))->resolve());
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        try {
            $dept = $this->departmentService->create($request->validated());

            return $this->success(
                (new DepartmentResource($dept))->resolve(),
                'Thêm bộ môn thành công',
                201
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage());
        }
    }

    public function update(UpdateDepartmentRequest $request, int $id): JsonResponse
    {
        try {
            $dept = $this->departmentService->update($id, $request->validated());

            return $this->success(
                (new DepartmentResource($dept))->resolve(),
                'Cập nhật thông tin bộ môn thành công'
            );
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'không tồn tại') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->departmentService->delete($id);

            return $this->success(null, 'Xóa bộ môn thành công');
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 404);
        }
    }

    public function sendSummaryEmail(SendDepartmentEmailRequest $request, int $id): JsonResponse
    {
        try {
            $this->departmentService->sendSummaryEmail(
                $id,
                $request->input('subject'),
                $request->input('body')
            );

            return $this->success(null, 'Đã gửi email về bộ môn thành công');
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'không tồn tại') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        } catch (\Throwable $e) {
            Log::error('[Admin\DepartmentController] Gửi email thất bại: ' . $e->getMessage());

            return $this->error('Gửi email thất bại. Vui lòng kiểm tra lại cấu hình mail server.', null, 500);
        }
    }
}
