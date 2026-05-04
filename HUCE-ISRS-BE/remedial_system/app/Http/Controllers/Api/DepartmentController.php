<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
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
        $departments = Department::orderBy('Name')->get();
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
        $dept = Department::find($id);
        if (!$dept) return $this->error('Bộ môn không tồn tại', null, 404);
        return $this->success($dept);
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
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", format: "email", example: "khcntt@vlu.edu.vn"),
                new OA\Property(property: "phone", type: "string", example: "0909123456"),
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Cập nhật thành công")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function update(Request $request, int $id): JsonResponse
    {
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
     * POST /api/admin/departments/{id}/send-email
     * Gửi email danh sách môn và sinh viên về bộ môn.
     */
    #[OA\Post(
        path: "/api/admin/departments/{id}/send-email",
        operationId: "sendDepartmentEmail",
        summary: "Gửi email danh sách sinh viên về bộ môn",
        security: [["sanctum" => []]],
        tags: ["Bộ môn"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "subject", type: "string", example: "Danh sách sinh viên học phụ đạo đợt 1"),
                new OA\Property(property: "body",    type: "string", example: "Gửi Bộ môn danh sách chi tiết các môn học và sinh viên đăng ký phụ đạo."),
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Gửi email thành công")]
    #[OA\Response(response: 400, description: "Email bộ môn chưa được cấu hình")]
    #[OA\Response(response: 500, description: "Gửi email thất bại")]
    public function sendSummaryEmail(Request $request, int $id): JsonResponse
    {
        $dept = Department::find($id);
        if (!$dept) return $this->error('Bộ môn không tồn tại', null, 404);

        if (!$dept->Email) {
            return $this->error('Bộ môn chưa được cấu hình địa chỉ Email.', null, 400);
        }

        // Lấy danh sách các lớp phụ đạo thuộc các môn của bộ môn này
        // Kèm theo Course và Enrollment (kèm Student)
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
