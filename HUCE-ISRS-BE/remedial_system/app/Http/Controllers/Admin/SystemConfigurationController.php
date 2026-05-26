<?php

namespace App\Http\Controllers\Admin;

use App\Application\Services\Admin\ManageSystemConfigurationService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\UpdateSettingsRequest;
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
}
