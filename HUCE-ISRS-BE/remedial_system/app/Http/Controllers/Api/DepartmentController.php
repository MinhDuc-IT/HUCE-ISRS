<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Application\Services\DepartmentService;
use App\Models\Department;
use App\Models\TutoringClass;
use App\Mail\DepartmentRemedialSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Bộ môn", description: "Quản lý thông tin Bộ môn và liên lạc")]
class DepartmentController extends BaseController
{
    public function __construct(
        private readonly DepartmentService $departmentService
    ) {}

    /**
     * GET /api/admin/departments
     */
    #[OA\Get(
        path: "/api/admin/departments",
        operationId: "listDepartments",
        summary: "Danh sách bộ môn",
        security: [["sanctum" => []]],
        tags: ["Bộ môn"],
    )]
    #[OA\Response(response: 200, description: "Thành công")]
    public function index(): JsonResponse
    {
        $departments = $this->departmentService->getAllDepartments();
        return $this->success($departments);
    }

    /**
     * GET /api/admin/departments/{id}
     */
    #[OA\Get(
        path: "/api/admin/departments/{id}",
        operationId: "showDepartment",
        summary: "Chi tiết bộ môn",
        security: [["sanctum" => []]],
        tags: ["Bộ môn"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Thành công")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function show(int $id): JsonResponse
    {
        $dept = $this->departmentService->getDepartmentDetail($id);
        if (!$dept) return $this->error('Bộ môn không tồn tại', null, 404);
        return $this->success($dept);
    }

    /**
     * POST /api/admin/departments
     */
    #[OA\Post(
        path: "/api/admin/departments",
        operationId: "storeDepartment",
        summary: "Thêm bộ môn mới",
        security: [["sanctum" => []]],
        tags: ["Bộ môn"],
    )]
    #[OA\Response(response: 201, description: "Thành công")]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'DepartmentCode' => 'required|string|max:50|unique:Department,DepartmentCode',
            'Name'           => 'required|string|max:255',
            'Email'          => 'nullable|email|max:255',
            'Phone'          => 'nullable|string|max:50',
        ]);

        $dept = Department::create([
            'DepartmentCode' => $request->DepartmentCode,
            'Name'           => $request->Name,
            'Email'          => $request->Email,
            'Phone'          => $request->Phone,
        ]);

        return $this->success($dept, 'Thêm bộ môn thành công', 201);
    }

    /**
     * PATCH /api/admin/departments/{id}
     * Sửa thông tin email và số điện thoại bộ môn.
     */
    #[OA\Patch(
        path: "/api/admin/departments/{id}",
        operationId: "updateDepartment",
        summary: "Sửa thông tin bộ môn",
        description: "Cập nhật email bộ môn và số điện thoại trưởng bộ môn.",
        security: [["sanctum" => []]],
        tags: ["Bộ môn"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Thành công")]
    #[OA\Response(response: 400, description: "Dữ liệu không hợp lệ")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function update(Request $request, int $id): JsonResponse
    {
        // Tạm thời giữ Eloquent để tránh refactor quá sâu vào repository/service nếu chưa cần thiết ngay.
        $dept = Department::find($id);
        if (!$dept) return $this->error('Bộ môn không tồn tại', null, 404);

        $request->validate([
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
        ]);

        $dept->update([
            'Email' => $request->input('email', $dept->Email),
            'Phone' => $request->input('phone', $dept->Phone),
        ]);

        return $this->success($dept, 'Cập nhật thông tin bộ môn thành công');
    }

    /**
     * DELETE /api/admin/departments/{id}
     */
    #[OA\Delete(
        path: "/api/admin/departments/{id}",
        operationId: "destroyDepartment",
        summary: "Xóa bộ môn",
        security: [["sanctum" => []]],
        tags: ["Bộ môn"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Xóa thành công")]
    public function destroy(int $id): JsonResponse
    {
        $dept = Department::find($id);
        if (!$dept) return $this->error('Bộ môn không tồn tại', null, 404);

        $dept->delete();
        return $this->success(null, 'Xóa bộ môn thành công');
    }

    /**
     * POST /api/admin/departments/{id}/send-email
     */
    #[OA\Post(
        path: "/api/admin/departments/{id}/send-email",
        operationId: "sendDepartmentEmail",
        summary: "Gửi email danh sách sinh viên về bộ môn",
        security: [["sanctum" => []]],
        tags: ["Bộ môn"],
    )]
    #[OA\Response(response: 200, description: "Gửi thành công")]
    #[OA\Response(response: 400, description: "Chưa cấu hình email")]
    #[OA\Response(response: 404, description: "Không có dữ liệu")]
    public function sendSummaryEmail(Request $request, int $id): JsonResponse
    {
        // Tạm thời giữ logic cũ nhưng có thể chuyển dần sang service.
        $dept = Department::find($id);
        if (!$dept) return $this->error('Bộ môn không tồn tại', null, 404);

        if (!$dept->Email) {
            return $this->error('Bộ môn chưa được cấu hình địa chỉ Email.', null, 400);
        }

        $tutoringClasses = TutoringClass::whereHas('course', function($q) use ($id) {
                $q->where('DepartmentId', $id);
            })
            ->with(['course', 'teacher', 'enrollments.student'])
            ->get();

        if ($tutoringClasses->isEmpty()) {
            return $this->error('Hiện không có môn học phụ đạo nào thuộc bộ môn này.', null, 404);
        }

        try {
            Mail::to($dept->Email)->send(new DepartmentRemedialSummary(
                department: $dept,
                tutoringClasses: $tutoringClasses,
                emailSubject: $request->subject,
                emailBody: $request->body
            ));

            return $this->success(null, 'Đã gửi email về bộ môn thành công');

        } catch (\Throwable $e) {
            Log::error("[DepartmentController] Gửi email thất bại: " . $e->getMessage());
            return $this->error('Gửi email thất bại. Vui lòng kiểm tra lại cấu hình mail server.', null, 500);
        }
    }
}
