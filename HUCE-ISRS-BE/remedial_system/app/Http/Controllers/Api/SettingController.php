<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\SystemConfig;
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
    /**
     * GET /api/admin/settings
     * Danh sách cấu hình (Admin xem)
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

        $config = SystemConfig::first() ?? new SystemConfig();

        return $this->success(
            data: [
                ['key' => 'min_students_per_class', 'value' => (string)$config->MinStudentsPerClass, 'description' => 'Sĩ số tối thiểu mỗi lớp'],
                ['key' => 'max_students_per_class', 'value' => (string)$config->MaxStudentsPerClass, 'description' => 'Sĩ số tối đa mỗi lớp'],
                ['key' => 'default_periods', 'value' => (string)$config->DefaultPeriods, 'description' => 'Số tiết mặc định'],
            ]
        );
    }

    /**
     * POST /api/admin/settings
     *
     * Use case : Cài đặt hệ thống
     * Actor    : Admin
     *
     * Normal Flow:
     *   1. Admin gọi API cập nhật cấu hình                      ← bước 1+2
     *   2. UpdateSettingsRequest kiểm tra tính hợp lệ            ← bước 3
     *   3. Hệ thống lưu lại cấu hình vào CSDL                    ← bước 4
     *   4. Trả về cấu hình mới
     *
     * Alternative Flow:
     *   AF-1: Lưu thất bại         → 500
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
                            new OA\Property(property: "key", type: "string", example: "teacher_rate_per_credit"),
                            new OA\Property(property: "value", type: "string", example: "150000"),
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Cập nhật thành công")]
    #[OA\Response(response: 403, description: "Không có quyền – chỉ Admin")]
    #[OA\Response(response: 422, description: "Thông tin không hợp lệ")]
    #[OA\Response(response: 500, description: "AF-1: Thất bại")]
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        // ── Bước 3: Đã được xử lý bởi UpdateSettingsRequest

        // ── Bước 4: Lưu vào CSDL
        try {
            $settingsData = $request->input('settings', []);

            $config = SystemConfig::first() ?? new SystemConfig();

            foreach ($settingsData as $item) {
                switch ($item['key']) {
                    case 'min_students_per_class':
                        $config->MinStudentsPerClass = (int)$item['value'];
                        break;
                    case 'max_students_per_class':
                        $config->MaxStudentsPerClass = (int)$item['value'];
                        break;
                    case 'default_periods':
                        $config->DefaultPeriods = (int)$item['value'];
                        break;
                }
            }
            $config->save();

        } catch (\Throwable $e) {
            // Alternative Flow 1: Lưu thất bại
            Log::error('[SettingController] Cập nhật cấu hình thất bại', [
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                message: 'Cập nhật cấu hình thất bại. Vui lòng thử lại.',
                status: 500
            );
        }

        return $this->success(
            data: [
                ['key' => 'min_students_per_class', 'value' => (string)$config->MinStudentsPerClass, 'description' => 'Sĩ số tối thiểu mỗi lớp'],
                ['key' => 'max_students_per_class', 'value' => (string)$config->MaxStudentsPerClass, 'description' => 'Sĩ số tối đa mỗi lớp'],
                ['key' => 'default_periods', 'value' => (string)$config->DefaultPeriods, 'description' => 'Số tiết mặc định'],
            ],
            message: 'Cập nhật cấu hình hệ thống thành công.',
        );
    }
}
