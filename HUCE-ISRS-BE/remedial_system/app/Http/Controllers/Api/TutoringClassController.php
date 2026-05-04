<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateTutoringClassRequest;
use App\Http\Requests\UpdateTutoringClassRequest;
use App\Models\TutoringClass;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Đợt phụ đạo",
    description: "Quản lý đợt phụ đạo – chỉ Admin"
)]
class TutoringClassController extends BaseController
{
    /**
     * POST /api/admin/tutoring-classes
     *
     * Use case : Thêm đợt phụ đạo
     * Actor    : Admin
     *
     * Normal Flow:
     *   1. Admin gọi API với thông tin đợt phụ đạo          ← bước 1+2
     *   2. CreateTutoringClassRequest validate đầu vào       ← bước 2+3
     *   3. Lưu vào CSDL                                      ← bước 4
     *   4. Trả về đợt phụ đạo vừa tạo                       ← bước 5
     *
     * Alternative Flow:
     *   AF-1: Thời gian không hợp lệ → 422 (từ request validation)
     *   AF-2: Lưu thất bại           → 500
     *   AF-3: Admin huỷ thao tác     → không gọi API (client tự xử lý)
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
                new OA\Property(property: "teacher_name",           type: "string",  nullable: true, example: "Nguyễn Văn A",
                    description: "Tên giảng viên"),

                new OA\Property(property: "max_students",           type: "integer", example: 30,
                    description: "Sĩ số tối đa"),
                new OA\Property(property: "note",                   type: "string",  nullable: true, example: "Ưu tiên sinh viên năm 3",
                    description: "Ghi chú"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Thêm đợt phụ đạo thành công – bước 5 Normal Flow",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string",  example: "Đợt phụ đạo đã được tạo thành công"),
                new OA\Property(property: "data",    type: "object"),
                new OA\Property(property: "errors",  type: "null"),
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Chưa xác thực")]
    #[OA\Response(response: 403, description: "Không có quyền – chỉ Admin")]
    #[OA\Response(response: 422, description: "AF-1: Thông tin không hợp lệ (thời gian sai)")]
    #[OA\Response(response: 500, description: "AF-2: Lỗi hệ thống khi lưu")]
    public function store(CreateTutoringClassRequest $request): JsonResponse
    {
        // ── Bước 2+3: Validation đã được xử lý bởi CreateTutoringClassRequest ─
        // Nếu thời gian không hợp lệ → Laravel tự trả 422 (Alternative Flow 1)

        // ── Bước 4: Lưu vào CSDL ─────────────────────────────────────────────
        try {
            $course = Course::where('CourseCode', strtoupper(trim($request->course_code)))->first();
            if (!$course) return $this->error('Môn học không tồn tại', null, 404);

            $teacherId = null;
            if ($request->teacher_code) {
                $teacher = Teacher::where('TeacherCode', strtoupper(trim($request->teacher_code)))->first();
                $teacherId = $teacher?->id;
            }

            $tutoringClass = TutoringClass::create([
                'CourseId'              => $course->id,
                'TutoringTermId'        => $request->tutoring_term_id,
                'TeacherId'             => $teacherId,
                'MaxStudents'           => $request->max_students,
                'CurrentStudents'       => 0,
                'Status'                => 'open',
            ]);

            // FIXME: StartDate, EndDate, RegistrationDeadline cần được chuyển sang lưu vào cấu hình hoặc model khác (như ClassSchedule) vì DB schema mới không có các cột này trên bảng TutoringClass.
            // Tạm thời bỏ qua các cột thời gian.

        } catch (\Throwable $e) {
            // Alternative Flow 2: Thêm thất bại
            return $this->error(
                message: 'Thêm đợt phụ đạo thất bại. Vui lòng thử lại.',
                status: 500
            );
        }

        // ── Bước 5: Trả về đợt phụ đạo vừa tạo ──────────────────────────────
        return $this->success(
            data: $this->formatTutoringClass($tutoringClass->load(['tutoringTerm', 'course', 'teacher'])),
            message: 'Đợt phụ đạo đã được tạo thành công',
            status: 201
        );
    }

    /**
     * GET /api/admin/tutoring-classes
     * Danh sách tất cả đợt phụ đạo (Admin xem).
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
    #[OA\Response(response: 200, description: "Danh sách đợt phụ đạo")]
    #[OA\Response(response: 403, description: "Không có quyền")]
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $query = TutoringClass::with(['tutoringTerm', 'course', 'teacher'])->orderByDesc('CreatedAt');

        if ($request->filled('tutoring_term_id')) {
            $query->where('TutoringTermId', $request->tutoring_term_id);
        }

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        $classes = $query->get();

        return $this->success(
            data: $classes->map(fn($c) => $this->formatTutoringClass((object)$c))->values(),
        );
    }

    /**
     * GET /api/admin/tutoring-classes/{id}
     * Chi tiết một đợt phụ đạo – bước 1 Normal Flow (chọn đợt muốn sửa).
     */
    #[OA\Get(
        path: "/api/admin/tutoring-classes/{id}",
        operationId: "showTutoringClass",
        summary: "Chi tiết đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Chi tiết đợt phụ đạo")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function show(Request $request, int $id): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $tutoringClass = TutoringClass::with(['tutoringTerm', 'course', 'teacher'])->find($id);

        if (! $tutoringClass) {
            return $this->error("Không tìm thấy đợt phụ đạo #{$id}.", null, 404);
        }

        return $this->success($this->formatTutoringClass($tutoringClass));
    }

    /**
     * PATCH /api/admin/tutoring-classes/{tutoringClass}
     *
     * Use case : Sửa đợt phụ đạo
     * Actor    : Admin
     *
     * Normal Flow:
     *   1. Admin chọn đợt phụ đạo (GET /{id} ở trên)              ← bước 1
     *   2. Gửi các trường cần sửa (PATCH – partial update)         ← bước 2
     *   3. UpdateTutoringClassRequest kiểm tra thời gian           ← bước 3
     *   4. Lưu thay đổi vào CSDL                                   ← bước 4
     *   5. Trả về thông tin đã cập nhật                            ← bước 5
     *
     * Alternative Flow:
     *   AF-1: Thời gian không hợp lệ           → 422
     *   AF-2: Cập nhật thất bại                → 500
     *   AF-3: Admin hủy (không gọi API)        → client tự xử lý
     *   Thêm: Đợt đã bị hủy (cancelled)        → 409 Conflict
     */
    #[OA\Patch(
        path: "/api/admin/tutoring-classes/{id}",
        operationId: "updateTutoringClass",
        summary: "Sửa đợt phụ đạo",
        description: "Partial update (PATCH): chỉ cần gửi các trường muốn sửa. Hệ thống tự kiểm tra ràng buộc thời gian (deadline < start_date < end_date).",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "course_code",           type: "string",  nullable: true, example: "CS101"),
                new OA\Property(property: "course_name",           type: "string",  nullable: true, example: "Lập trình hướng đối tượng"),
                new OA\Property(property: "credits",               type: "integer", nullable: true, example: 3),
                new OA\Property(property: "tutoring_term_id",      type: "integer", nullable: true, example: 1),
                new OA\Property(property: "teacher_code",          type: "string",  nullable: true, example: "GV001"),
                new OA\Property(property: "teacher_name",          type: "string",  nullable: true, example: "Nguyễn Văn A"),

                new OA\Property(property: "max_students",          type: "integer", nullable: true, example: 30),
                new OA\Property(property: "note",                  type: "string",  nullable: true, example: "Đã cập nhật phòng học"),
            ]
        )
    )]
    #[OA\Response(response: 200,  description: "Bước 5: Thông tin đợt phụ đạo đã chỉnh sửa")]
    #[OA\Response(response: 403,  description: "Không có quyền – chỉ Admin")]
    #[OA\Response(response: 404,  description: "Không tìm thấy đợt phụ đạo")]
    #[OA\Response(response: 409,  description: "Đợt đã bị hủy, không thể chỉnh sửa")]
    #[OA\Response(response: 422,  description: "AF-1: Thời gian không hợp lệ")]
    #[OA\Response(response: 500,  description: "AF-2: Chỉnh sửa thất bại")]
    public function update(UpdateTutoringClassRequest $request, int $id): JsonResponse
    {
        // ── Bước 1: Route model binding tự tìm và trả 404 nếu không có ─────────
        // ── Bước 2+3: Validation đã xử lý bởi UpdateTutoringClassRequest ───────

        $tutoringClass = TutoringClass::find($id);
        if (!$tutoringClass) return $this->error('Không tìm thấy đợt phụ đạo', null, 404);

        // Không cho phép sửa đợt đã bị hủy
        if ($tutoringClass->Status === 'cancelled') {
            return $this->error(
                message: 'Không thể chỉnh sửa đợt phụ đạo đã bị hủy.',
                status: 409
            );
        }

        // ── Bước 4: Cập nhật CSDL ─────────────────────────────────────────────
        try {
            $updates = [];
            
            if ($request->has('course_code')) {
                $course = Course::where('CourseCode', strtoupper(trim($request->course_code)))->first();
                if ($course) $updates['CourseId'] = $course->id;
            }
            if ($request->has('tutoring_term_id')) {
                $updates['TutoringTermId'] = $request->tutoring_term_id;
            }
            if ($request->has('teacher_code')) {
                $teacher = Teacher::where('TeacherCode', strtoupper(trim($request->teacher_code)))->first();
                $updates['TeacherId'] = $teacher?->id;
            }
            if ($request->has('max_students')) {
                $updates['MaxStudents'] = $request->max_students;
            }

            if (!empty($updates)) {
                $tutoringClass->update($updates);
            }

        } catch (\Throwable) {
            // Alternative Flow 2: Chỉnh sửa thất bại
            return $this->error(
                message: 'Chỉnh sửa đợt phụ đạo thất bại. Vui lòng thử lại.',
                status: 500
            );
        }

        // ── Bước 5: Trả về thông tin đã cập nhật ─────────────────────────────
        return $this->success(
            data: $this->formatTutoringClass($tutoringClass->fresh(['tutoringTerm', 'course', 'teacher'])),
            message: 'Đợt phụ đạo đã được cập nhật thành công',
        );
    }

    /**
     * DELETE /api/admin/tutoring-classes/{tutoringClass}
     *
     * Use case : Xóa đợt phụ đạo
     * Actor    : Admin
     *
     * Normal Flow:
     *   1. Admin chọn đợt muốn xóa (GET /{id})                    ← bước 1
     *   2. Xác nhận xóa (client gửi DELETE request)               ← bước 2
     *   3. Kiểm tra sinh viên đã đăng ký                          ← bước 3
     *   4. Xóa khỏi CSDL                                          ← bước 4
     *   5. Loại bỏ khỏi danh sách (không trả data, chỉ 200)       ← bước 5
     *
     * Alternative Flow:
     *   AF-1: Có sinh viên đăng ký → 409 Conflict (không được xóa)
     *   AF-2: Xóa thất bại         → 500
     *   AF-3: Admin hủy thao tác   → client không gọi API
     */
    #[OA\Delete(
        path: "/api/admin/tutoring-classes/{id}",
        operationId: "deleteTutoringClass",
        summary: "Xóa đợt phụ đạo",
        description: "Xóa vĩnh viễn đợt phụ đạo. Bước 3: hệ thống từ chối nếu có sinh viên đang đăng ký (status pending hoặc approved).",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200,  description: "Bước 5: Xóa thành công")]
    #[OA\Response(response: 403,  description: "Không có quyền – chỉ Admin")]
    #[OA\Response(response: 404,  description: "Không tìm thấy đợt phụ đạo")]
    #[OA\Response(response: 409,  description: "AF-1: Có sinh viên đăng ký, không thể xóa")]
    #[OA\Response(response: 500,  description: "AF-2: Xóa thất bại")]
    /**
     * PATCH /api/admin/tutoring-classes/{id}/assign-teacher
     * Phân công giảng viên cho môn học.
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
    public function assignTeacher(Request $request, int $id): JsonResponse
    {
        $tutoringClass = TutoringClass::with('course')->find($id);
        if (!$tutoringClass) return $this->error('Không tìm thấy đợt phụ đạo', null, 404);

        $user = $request->user();

        // Kiểm tra quyền: Admin hoặc Bộ môn của môn học đó
        if (!$user->isAdmin()) {
            if (!$user->isBoMon() || $user->department_id !== $tutoringClass->course->DepartmentId) {
                return $this->error('Bạn không có quyền phân công cho môn học này.', null, 403);
            }
        }

        $request->validate([
            'teacher_code' => 'required|string|exists:Teacher,TeacherCode',
        ]);

        $teacher = Teacher::where('TeacherCode', $request->teacher_code)->first();
        
        $tutoringClass->update([
            'TeacherId' => $teacher->id
        ]);

        return $this->success(
            $this->formatTutoringClass($tutoringClass->load('teacher')),
            'Phân công giảng viên thành công'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        // ── Bước 1: Route model binding tự tìm, trả 404 nếu không có ──────────
        // ── Bước 2: Xác nhận xóa = Admin gửi DELETE request ───────────────────

        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $tutoringClass = TutoringClass::with('course')->find($id);
        if (!$tutoringClass) return $this->error('Không tìm thấy đợt phụ đạo', null, 404);

        // ── Bước 3: Kiểm tra có sinh viên đăng ký không ────────────────────────
        $activeCount = $this->countActiveRegistrations($tutoringClass);

        if ($activeCount > 0) {
            // Alternative Flow 1: có sinh viên đăng ký → từ chối xóa
            return $this->error(
                message: "Không thể xóa đợt phụ đạo đang có {$activeCount} sinh viên đăng ký.",
                status: 409
            );
        }

        // ── Bước 4: Xóa khỏi CSDL ─────────────────────────────────────────────
        try {
            $tutoringClass->delete();

        } catch (\Throwable $e) {
            // Alternative Flow 2: xóa thất bại
            Log::error('[TutoringClassController] Xóa đợt phụ đạo thất bại', [
                'id'    => $tutoringClass->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                message: 'Xóa đợt phụ đạo thất bại. Vui lòng thử lại.',
                status: 500
            );
        }

        // ── Bước 5: Loại bỏ khỏi danh sách – trả về thành công ────────────────
        return $this->success(
            data: null,
            message: "Đã xóa đợt phụ đạo thành công.",
        );
    }

    // =========================================================================
    // Helpers
    // =========================================================================


    /**
     * Bước 5 Normal Flow: format dữ liệu trả về client.
     */
    private function formatTutoringClass(object $model): array
    {
        return [
            'id'                    => $model->id,
            'courseCode'            => $model->course?->CourseCode,
            'courseName'            => $model->course?->CourseName,
            'credits'               => $model->course?->Credits,
            'tutoringTerm'          => $model->tutoringTerm ? [
                'id'         => $model->tutoringTerm->id,
                'name'       => $model->tutoringTerm->Name,
                'startDate'  => $model->tutoringTerm->StartDate?->toDateString(),
                'endDate'    => $model->tutoringTerm->EndDate?->toDateString(),
            ] : null,
            'teacherCode'           => $model->teacher?->TeacherCode,
            'teacherName'           => $model->teacher?->FullName,
            'maxStudents'           => $model->MaxStudents,
            'status'                => $model->Status,
            'createdAt'             => $model->CreatedAt,
        ];
    }

    /**
     * Đếm số sinh viên đã đăng ký chưa hủy cho đợt phụ đạo này.
     *
     * Kiểm tra qua bảng Enrollment khớp theo TutoringClassId.
     */
    private function countActiveRegistrations(TutoringClass $tutoringClass): int
    {
        return DB::table('Enrollment')
            ->where('TutoringClassId', $tutoringClass->id)
            ->where('Status', 'active')
            ->count();
    }
}
