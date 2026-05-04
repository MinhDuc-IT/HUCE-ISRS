<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UpdateSettingsRequest;
use App\Domain\Repositories\SystemConfigRepositoryPort;
use App\Domain\Enums\SystemConfigKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Cài đặt hệ thống",
    description: "Quản lý các cấu hình hệ thống – chỉ Admin"
)]
class SettingController extends BaseController
{
    public function __construct(
        private readonly SystemConfigRepositoryPort $configRepository
    ) {}

    /**
     * GET /api/admin/settings
     */
    #[OA\Get(
        path: "/api/admin/settings",
        operationId: "listSettings",
        summary: "Lấy danh sách cấu hình hệ thống",
        security: [["sanctum" => []]],
        tags: ["Cài đặt hệ thống"],
    )]
    #[OA\Response(response: 200, description: "Danh sách cấu hình")]
    #[OA\Response(response: 403, description: "Không có quyền")]
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $allConfigs = $this->configRepository->all();

        return $this->success(
            data: array_map(fn($c) => [
                'key'         => $c->key,
                'value'       => $c->value,
                'description' => $c->description
            ], $allConfigs)
        );
    }

    /**
     * POST /api/admin/settings
     */
    #[OA\Post(
        path: "/api/admin/settings",
        operationId: "updateSettings",
        summary: "Cập nhật cấu hình hệ thống",
        description: "Cập nhật nhiều cấu hình cùng lúc truyền lên dạng mảng.",
        security: [["sanctum" => []]],
        tags: ["Cài đặt hệ thống"],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["settings"],
            properties: [
                new OA\Property(
                    property: "settings",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "key", type: "string", example: "mail_summary_subject"),
                            new OA\Property(property: "value", type: "string", example: "Thông báo học phụ đạo"),
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Cập nhật thành công")]
    #[OA\Response(response: 403, description: "Không có quyền – chỉ Admin")]
    #[OA\Response(response: 422, description: "Thông tin không hợp lệ")]
    #[OA\Response(response: 500, description: "Cập nhật thất bại")]
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        try {
            $settingsData = $request->input('settings', []);

            foreach ($settingsData as $item) {
                $this->configRepository->set($item['key'], $item['value'] ?? '');
            }

            $updatedConfigs = $this->configRepository->all();

            return $this->success(
                data: array_map(fn($c) => [
                    'key'         => $c->key,
                    'value'       => $c->value,
                    'description' => $c->description
                ], $updatedConfigs),
                message: 'Cập nhật cấu hình hệ thống thành công.',
            );

        } catch (\Throwable $e) {
            Log::error('[SettingController] Cập nhật cấu hình thất bại', [
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                message: 'Cập nhật cấu hình thất bại. Vui lòng thử lại.',
                status: 500
            );
        }
    }
}
