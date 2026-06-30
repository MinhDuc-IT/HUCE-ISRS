<?php

namespace App\Http\Controllers\Admin;

use App\Application\Services\Admin\ManageSystemConfigurationService;
use App\Http\Controllers\BaseController;
use App\Http\Middleware\FakeTodayMiddleware;
use App\Http\Requests\Admin\StoreSystemConfigurationRequest;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Http\Requests\Admin\UpdateSystemConfigurationItemRequest;
use App\Http\Resources\SystemConfigurationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Cài đặt hệ thống', description: 'Quản lý các cấu hình hệ thống – chỉ Admin')]
class SystemConfigurationController extends BaseController
{
    public function __construct(
        private readonly ManageSystemConfigurationService $configService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(
            SystemConfigurationResource::collection($this->configService->list())->resolve()
        );
    }

    public function store(StoreSystemConfigurationRequest $request): JsonResponse
    {
        try {
            $config = $this->configService->create(
                key: trim((string) $request->input('key')),
                value: (string) $request->input('value', ''),
                description: $request->input('description')
            );

            return $this->success(
                (new SystemConfigurationResource($config))->resolve(),
                'Tạo cấu hình hệ thống thành công.',
                201
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            Log::error('[Admin\SystemConfigurationController] Tạo cấu hình thất bại', ['error' => $e->getMessage()]);

            return $this->error('Tạo cấu hình thất bại. Vui lòng thử lại.', null, 500);
        }
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        try {
            $configs = $this->configService->updateMany($request->input('settings', []));

            return $this->success(
                SystemConfigurationResource::collection($configs)->resolve(),
                'Cập nhật cấu hình hệ thống thành công.'
            );
        } catch (\Throwable $e) {
            Log::error('[Admin\SystemConfigurationController] Cập nhật thất bại', ['error' => $e->getMessage()]);

            return $this->error('Cập nhật cấu hình thất bại. Vui lòng thử lại.', null, 500);
        }
    }

    public function updateItem(UpdateSystemConfigurationItemRequest $request, string $key): JsonResponse
    {
        try {
            $config = $this->configService->update(
                key: $key,
                value: $request->input('value'),
                description: $request->has('description') ? $request->input('description') : null
            );

            FakeTodayMiddleware::clearCacheForKey($key);

            return $this->success(
                (new SystemConfigurationResource($config))->resolve(),
                'Cập nhật cấu hình hệ thống thành công.'
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            Log::error('[Admin\SystemConfigurationController] Cập nhật thất bại', ['error' => $e->getMessage()]);

            return $this->error('Cập nhật cấu hình thất bại. Vui lòng thử lại.', null, 500);
        }
    }

    public function destroy(string $key): JsonResponse
    {
        try {
            $this->configService->delete($key);

            return $this->success(null, 'Xóa cấu hình hệ thống thành công.');
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            Log::error('[Admin\SystemConfigurationController] Xóa cấu hình thất bại', ['error' => $e->getMessage()]);

            return $this->error('Xóa cấu hình thất bại. Vui lòng thử lại.', null, 500);
        }
    }
}
