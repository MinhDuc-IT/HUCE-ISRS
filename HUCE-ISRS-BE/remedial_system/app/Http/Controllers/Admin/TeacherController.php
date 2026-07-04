<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Giảng viên', description: 'Quản lý giảng viên – chỉ Admin')]
class TeacherController extends BaseController
{
    #[OA\Get(
        path: '/api/admin/teachers',
        operationId: 'listTeachers',
        summary: 'Danh sách tất cả giảng viên',
        security: [['sanctum' => []]],
        tags: ['Giảng viên'],
    )]
    #[OA\Response(response: 200, description: 'Danh sách giảng viên')]
    public function index(): JsonResponse
    {
        try {
            $teachers = Teacher::all();
            return $this->success($teachers->toArray());
        } catch (\Throwable $e) {
            Log::error('[Admin\TeacherController] Lỗi lấy danh sách giảng viên', ['error' => $e->getMessage()]);
            return $this->error('Lỗi lấy danh sách giảng viên. Vui lòng thử lại.', null, 500);
        }
    }

    #[OA\Post(
        path: '/api/admin/teachers',
        operationId: 'createTeacher',
        summary: 'Tạo mới giảng viên',
        security: [['sanctum' => []]],
        tags: ['Giảng viên'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'department_id', type: 'integer', example: 1),
                new OA\Property(property: 'first_name', type: 'string', example: 'Nguyễn'),
                new OA\Property(property: 'last_name', type: 'string', example: 'Văn A'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'nguyenvana@huce.edu.vn'),
                new OA\Property(property: 'phone', type: 'string', example: '0123456789'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Tạo giảng viên thành công')]
    #[OA\Response(response: 422, description: 'Dữ liệu đầu vào không hợp lệ')]
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'department_id' => 'required|integer|exists:departments,id',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:teachers',
                'phone' => 'required|string|max:20',
            ]);

            $teacher = Teacher::create($validated);

            return $this->success(
                $teacher->toArray(),
                'Thêm giảng viên thành công',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Dữ liệu không hợp lệ', $e->errors(), 422);
        } catch (\Throwable $e) {
            Log::error('[Admin\TeacherController] Thêm giảng viên thất bại', ['error' => $e->getMessage()]);
            return $this->error('Thêm giảng viên thất bại. Vui lòng thử lại.', null, 500);
        }
    }

    #[OA\Get(
        path: '/api/admin/teachers/{id}',
        operationId: 'getTeacher',
        summary: 'Chi tiết giảng viên',
        security: [['sanctum' => []]],
        tags: ['Giảng viên'],
    )]
    #[OA\Response(response: 200, description: 'Chi tiết giảng viên')]
    #[OA\Response(response: 404, description: 'Giảng viên không tồn tại')]
    public function show(int $id): JsonResponse
    {
        $teacher = Teacher::find($id);

        if ($teacher === null) {
            return $this->error("Không tìm thấy giảng viên #{$id}.", null, 404);
        }

        return $this->success($teacher->toArray());
    }

    #[OA\Patch(
        path: '/api/admin/teachers/{id}',
        operationId: 'updateTeacher',
        summary: 'Cập nhật giảng viên',
        security: [['sanctum' => []]],
        tags: ['Giảng viên'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'department_id', type: 'integer', example: 1),
                new OA\Property(property: 'first_name', type: 'string', example: 'Nguyễn'),
                new OA\Property(property: 'last_name', type: 'string', example: 'Văn A'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'nguyenvana@huce.edu.vn'),
                new OA\Property(property: 'phone', type: 'string', example: '0123456789'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Cập nhật thành công')]
    #[OA\Response(response: 404, description: 'Giảng viên không tồn tại')]
    public function update(int $id, Request $request): JsonResponse
    {
        $teacher = Teacher::find($id);

        if ($teacher === null) {
            return $this->error("Không tìm thấy giảng viên #{$id}.", null, 404);
        }

        try {
            $validated = $request->validate([
                'department_id' => 'sometimes|integer|exists:departments,id',
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => "sometimes|string|email|max:255|unique:teachers,email,{$id}",
                'phone' => 'sometimes|string|max:20',
            ]);

            $teacher->update($validated);

            return $this->success(
                $teacher->toArray(),
                'Cập nhật giảng viên thành công'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Dữ liệu không hợp lệ', $e->errors(), 422);
        } catch (\Throwable $e) {
            Log::error('[Admin\TeacherController] Cập nhật thất bại', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Cập nhật giảng viên thất bại. Vui lòng thử lại.', null, 500);
        }
    }

    #[OA\Delete(
        path: '/api/admin/teachers/{id}',
        operationId: 'deleteTeacher',
        summary: 'Xóa giảng viên',
        security: [['sanctum' => []]],
        tags: ['Giảng viên'],
    )]
    #[OA\Response(response: 200, description: 'Xóa thành công')]
    #[OA\Response(response: 404, description: 'Giảng viên không tồn tại')]
    public function destroy(int $id): JsonResponse
    {
        $teacher = Teacher::find($id);

        if ($teacher === null) {
            return $this->error("Không tìm thấy giảng viên #{$id}.", null, 404);
        }

        try {
            $teacher->delete();
            return $this->success(null, "Đã xóa giảng viên thành công");
        } catch (\Throwable $e) {
            Log::error('[Admin\TeacherController] Xóa thất bại', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Xóa giảng viên thất bại. Vui lòng thử lại.', null, 500);
        }
    }
}
