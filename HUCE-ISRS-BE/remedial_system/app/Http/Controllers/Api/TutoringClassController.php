<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\TutoringClassService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateTutoringClassRequest;
use App\Http\Requests\UpdateTutoringClassRequest;
use App\Domain\Entities\TutoringClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Đợt phụ đạo",
    description: "Quản lý đợt phụ đạo – chỉ Admin"
)]
class TutoringClassController extends BaseController
{
    public function __construct(
        private readonly TutoringClassService $classService
    ) {}

    /**
     * POST /api/admin/tutoring-classes
     */
    #[OA\Post(
        path: "/api/admin/tutoring-classes",
        operationId: "createTutoringClass",
        summary: "Thêm đợt phụ đạo mới",
        description: "Tạo một đợt phụ đạo mới. Hệ thống kiểm tra ràng buộc thời gian: hạn đăng ký phải trước ngày bắt đầu, ngày kết thúc phải sau ngày bắt đầu.",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
             required: ["course_code", "course_name", "credits", "tutoring_term_id",
                        "max_students"],
            properties: [
                new OA\Property(property: "course_code",            type: "string",  example: "CS101",
                    description: "Mã môn học"),
                new OA\Property(property: "course_name",            type: "string",  example: "Lập trình hướng đối tượng",
                    description: "Tên môn học"),
                new OA\Property(property: "credits",                type: "integer", example: 3,
                    description: "Số tín chỉ"),
                new OA\Property(property: "tutoring_term_id",       type: "integer", example: 1,
                    description: "ID đợt phụ đạo"),
                new OA\Property(property: "teacher_code",           type: "string",  nullable: true, example: "GV001",
                    description: "Mã giảng viên (tuỳ chọn)"),
                new OA\Property(property: "teacher_name",           type: "string",  nullable: true, example: "Nguyễn Văn A"),

                new OA\Property(property: "max_students",           type: "integer", example: 30,
                    description: "Sĩ số tối đa"),
                new OA\Property(property: "note",                   type: "string",  nullable: true, example: "Ưu tiên sinh viên năm 3",
                    description: "Ghi chú"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Thêm đợt phụ đạo thành công",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string",  example: "Đợt phụ đạo đã được tạo thành công"),
                new OA\Property(property: "data",    type: "object"),
                new OA\Property(property: "errors",  type: "null"),
            ]
        )
    )]
    public function store(CreateTutoringClassRequest $request): JsonResponse
    {
        try {
            $tutoringClass = $this->classService->createClass($request->validated());
            return $this->success(
                data: $this->formatTutoringClass($tutoringClass),
                message: 'Đợt phụ đạo đã được tạo thành công',
                status: 201
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            return $this->error('Thêm đợt phụ đạo thất bại.', null, 500);
        }
    }

    /**
     * GET /api/admin/tutoring-classes
     */
    #[OA\Get(
        path: "/api/admin/tutoring-classes",
        operationId: "listTutoringClasses",
        summary: "Danh sách đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "tutoring_term_id", in: "query", required: false,
        schema: new OA\Schema(type: "integer"), description: "Lọc theo đợt phụ đạo")]
    #[OA\Parameter(name: "status", in: "query", required: false,
        schema: new OA\Schema(type: "string", enum: ["open","full","closed","cancelled"]),
        description: "Lọc theo trạng thái")]
    #[OA\Response(response: 200, description: "Thành công")]
    #[OA\Response(response: 403, description: "Không có quyền")]
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $filters = $request->only(['tutoring_term_id', 'status']);
        $classes = $this->classService->listClasses($filters);

        return $this->success(
            data: collect($classes)->map(fn($c) => $this->formatTutoringClass($c))->values(),
        );
    }

    /**
     * GET /api/admin/tutoring-classes/{id}
     */
    #[OA\Get(
        path: "/api/admin/tutoring-classes/{id}",
        operationId: "showTutoringClass",
        summary: "Chi tiết đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Thành công")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function show(Request $request, int $id): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $tutoringClass = $this->classService->getClassDetail($id);

        if (! $tutoringClass) {
            return $this->error("Không tìm thấy đợt phụ đạo #{$id}.", null, 404);
        }

        return $this->success($this->formatTutoringClass($tutoringClass));
    }

    /**
     * PATCH /api/admin/tutoring-classes/{id}
     */
    #[OA\Patch(
        path: "/api/admin/tutoring-classes/{id}",
        operationId: "updateTutoringClass",
        summary: "Sửa đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Cập nhật thành công")]
    #[OA\Response(response: 400, description: "Dữ liệu không hợp lệ")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function update(UpdateTutoringClassRequest $request, int $id): JsonResponse
    {
        try {
            $tutoringClass = $this->classService->updateClass($id, $request->validated());
            return $this->success(
                data: $this->formatTutoringClass($tutoringClass),
                message: 'Đợt phụ đạo đã được cập nhật thành công',
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        } catch (\Throwable) {
            return $this->error('Chỉnh sửa đợt phụ đạo thất bại.', null, 500);
        }
    }

    /**
     * PATCH /api/admin/tutoring-classes/{id}/assign-teacher
     */
    #[OA\Patch(
        path: "/api/admin/tutoring-classes/{id}/assign-teacher",
        operationId: "assignTeacher",
        summary: "Phân công giảng viên phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "teacher_code", type: "string", example: "GV001"),
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Phân công thành công")]
    #[OA\Response(response: 400, description: "Dữ liệu không hợp lệ")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function assignTeacher(Request $request, int $id): JsonResponse
    {
        $tutoringClass = $this->classService->getClassDetail($id);
        if (!$tutoringClass) return $this->error('Không tìm thấy đợt phụ đạo', null, 404);

        $user = $request->user();
        if (!$user->isAdmin()) {
             // Logic check quyền bổ sung nếu cần
        }

        $request->validate([
            'teacher_code' => 'required|string',
        ]);

        try {
            $updatedClass = $this->classService->assignTeacher($id, $request->teacher_code);
            return $this->success(
                $this->formatTutoringClass($updatedClass),
                'Phân công giảng viên thành công'
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    /**
     * DELETE /api/admin/tutoring-classes/{id}
     */
    #[OA\Delete(
        path: "/api/admin/tutoring-classes/{id}",
        operationId: "deleteTutoringClass",
        summary: "Xóa đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Xóa thành công")]
    #[OA\Response(response: 409, description: "Xung đột dữ liệu")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        try {
            $this->classService->deleteClass($id);
            return $this->success(null, "Đã xóa đợt phụ đạo thành công.");
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 409);
        } catch (\Throwable $e) {
            return $this->error('Xóa đợt phụ đạo thất bại.', null, 500);
        }
    }

    /**
     * Format dữ liệu trả về client từ Domain Entity.
     */
    private function formatTutoringClass(TutoringClass $entity): array
    {
        return [
            'id'              => $entity->id,
            'courseId'        => $entity->courseId,
            'tutoringTermId'  => $entity->tutoringTermId,
            'teacherId'       => $entity->teacherId,
            'maxStudents'     => $entity->maxStudents,
            'currentStudents' => $entity->currentStudents,
            'status'          => $entity->status,
            'createdAt'       => $entity->createdAt->toDateTimeString(),
        ];
    }
}
