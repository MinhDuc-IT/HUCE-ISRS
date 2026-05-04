<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Người dùng",
    description: "Quản lý người dùng hệ thống – chỉ Admin"
)]
class UserController extends BaseController
{
    /**
     * POST /api/admin/users
     *
     * Use case : Thêm người dùng
     * Actor    : Admin
     *
     * Normal Flow:
     *   1. Admin gọi API thêm người dùng                        ← bước 1
     *   2. Nhập thông tin người dùng                            ← bước 2
     *   3. CreateUserRequest kiểm tra tính hợp lệ               ← bước 3
     *   4. Thêm người dùng vào CSDL                             ← bước 4
     *   5. Hiển thị thông tin người dùng vừa thêm               ← bước 5
     *
     * Alternative Flow:
     *   AF-1: Thông tin không hợp lệ → 422 (từ request validation)
     *   AF-2: Thêm thất bại          → 500
     *   AF-3: Hủy thao tác           → Client tự xử lý (không gọi API)
     */
    #[OA\Post(
        path: "/api/admin/users",
        operationId: "createUser",
        summary: "Thêm người dùng mới",
        description: "Tạo tài khoản người dùng mới. Ràng buộc: student_code bắt buộc nếu role=sinh_vien, department_id bắt buộc nếu role=bo_mon.",
        security: [["sanctum" => []]],
        tags: ["Người dùng"],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "password", "role"],
            properties: [
                new OA\Property(property: "name",          type: "string", example: "Nguyễn Văn A", description: "Họ tên"),
                new OA\Property(property: "email",         type: "string", format: "email", example: "nguyenvana@remedial.edu.vn"),
                new OA\Property(property: "password",      type: "string", format: "password", example: "password123"),
                new OA\Property(property: "role",          type: "string", enum: ["admin", "bo_mon", "sinh_vien"], example: "bo_mon"),
                new OA\Property(property: "student_code",  type: "string", nullable: true, example: null, description: "Bắt buộc nếu role là sinh_vien"),
                new OA\Property(property: "department_id", type: "integer", nullable: true, example: 1, description: "Bắt buộc nếu role là bo_mon"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Thêm thành công – bước 5 Normal Flow",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string",  example: "Thêm người dùng thành công"),
                new OA\Property(property: "data",    type: "object"),
                new OA\Property(property: "errors",  type: "null"),
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Chưa xác thực")]
    #[OA\Response(response: 403, description: "Không có quyền – chỉ Admin")]
    #[OA\Response(response: 422, description: "AF-1: Thông tin không hợp lệ")]
    #[OA\Response(response: 500, description: "AF-2: Thêm thất bại")]
    public function store(CreateUserRequest $request): JsonResponse
    {
        // ── Bước 2+3: Nhập thông tin và kiểm tra hợp lệ đã được xử lý bởi CreateUserRequest
        
        // ── Bước 4: Thêm người dùng vào cơ sở dữ liệu
        try {
            $user = clone $request; // just to prevent modification

            $userData = [
                'name'          => trim($request->name),
                'email'         => trim($request->email),
                'password'      => Hash::make($request->password),
                'role'          => $request->role,
                'student_code'  => $request->role === User::ROLE_SINH_VIEN ? strtoupper(trim($request->student_code)) : null,
                'department_id' => $request->role === User::ROLE_BO_MON ? $request->department_id : null,
            ];

            $newUser = User::create($userData);

        } catch (\Throwable $e) {
            // Alternative Flow 2: Thêm thất bại
            Log::error('[UserController] Thêm người dùng thất bại', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);

            return $this->error(
                message: 'Thêm người dùng thất bại. Vui lòng thử lại.',
                status: 500
            );
        }

        // ── Bước 5: Hiển thị người dùng được thêm mới
        return $this->success(
            data: $this->formatUser($newUser),
            message: 'Thêm người dùng thành công',
            status: 201
        );
    }

    /**
     * GET /api/admin/users
     * Danh sách người dùng (Admin xem).
     */
    #[OA\Get(
        path: "/api/admin/users",
        operationId: "listUsers",
        summary: "Danh sách người dùng",
        security: [["sanctum" => []]],
        tags: ["Người dùng"],
    )]
    #[OA\Parameter(name: "role", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["admin","bo_mon","sinh_vien"]), description: "Lọc theo vai trò")]
    #[OA\Response(response: 200, description: "Danh sách người dùng")]
    #[OA\Response(response: 403, description: "Không có quyền")]
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $query = User::orderByDesc('created_at');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->get();

        return $this->success(
            data: $users->map(fn($u) => $this->formatUser($u))->values(),
        );
    }
    
    /**
     * GET /api/admin/users/{id}
     * Lấy thông tin chi tiết một người dùng – bước 1 Normal Flow (chọn người dùng)
     */
    #[OA\Get(
        path: "/api/admin/users/{id}",
        operationId: "showUser",
        summary: "Chi tiết người dùng",
        security: [["sanctum" => []]],
        tags: ["Người dùng"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Chi tiết người dùng")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function show(Request $request, int $id): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $user = User::find($id);

        if (! $user) {
            return $this->error("Không tìm thấy người dùng #{$id}.", null, 404);
        }

        return $this->success($this->formatUser($user));
    }

    /**
     * PATCH /api/admin/users/{user}
     *
     * Use case : Sửa người dùng
     * Actor    : Admin
     *
     * Normal Flow:
     *   1. Admin chọn người dùng (GET /{id} ở trên)               ← bước 1
     *   2. Gửi các trường cần sửa (PATCH)                         ← bước 2
     *   3. UpdateUserRequest kiểm tra tính hợp lệ                 ← bước 3
     *   4. Cập nhật thông tin vào CSDL                            ← bước 4
     *   5. Trả về thông tin đã cập nhật                           ← bước 5
     *
     * Alternative Flow:
     *   AF-1: Thông tin không hợp lệ → 422
     *   AF-2: Chỉnh sửa thất bại     → 500
     *   AF-3: Admin hủy              → Client tự xử lý (không gọi API)
     */
    #[OA\Patch(
        path: "/api/admin/users/{id}",
        operationId: "updateUser",
        summary: "Sửa người dùng",
        description: "Partial update (PATCH). Cập nhật thông tin người dùng. Nếu đổi role sang sinh_vien cần kèm student_code.",
        security: [["sanctum" => []]],
        tags: ["Người dùng"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name",          type: "string", nullable: true, example: "Nguyễn Văn B"),
                new OA\Property(property: "email",         type: "string", format: "email", nullable: true, example: "nguyenvanb@remedial.edu.vn"),
                new OA\Property(property: "password",      type: "string", format: "password", nullable: true, example: "newpassword123"),
                new OA\Property(property: "role",          type: "string", enum: ["admin", "bo_mon", "sinh_vien"], nullable: true, example: "sinh_vien"),
                new OA\Property(property: "student_code",  type: "string", nullable: true, example: "SV002"),
                new OA\Property(property: "department_id", type: "integer", nullable: true, example: null),
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Bước 5: Cập nhật thành công")]
    #[OA\Response(response: 403, description: "Không có quyền – chỉ Admin")]
    #[OA\Response(response: 404, description: "Không tìm thấy người dùng")]
    #[OA\Response(response: 422, description: "AF-1: Thông tin không hợp lệ")]
    #[OA\Response(response: 500, description: "AF-2: Cập nhật thất bại")]
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        // ── Bước 1: Route model binding tự tìm và trả 404 nếu không có
        // ── Bước 2+3: Nhập thông tin & kiểm tra hợp lệ đã xử lý bởi UpdateUserRequest
        
        // ── Bước 4: Chỉnh sửa vào cơ sở dữ liệu
        try {
            $updateData = [];

            if ($request->has('name'))          $updateData['name'] = trim($request->name);
            if ($request->has('email'))         $updateData['email'] = trim($request->email);
            if ($request->has('password'))      $updateData['password'] = Hash::make($request->password);
            if ($request->has('role'))          $updateData['role'] = $request->role;
            
            // Xử lý logic role:
            // Role lấy từ request nếu có sửa, nếu không giữ role cũ
            $targetRole = $request->input('role', $user->role);
            
            if ($request->has('student_code')) {
                $updateData['student_code'] = $targetRole === User::ROLE_SINH_VIEN ? strtoupper(trim($request->student_code)) : null;
            } elseif ($request->has('role')) {
                // Nếu đổi role nhưng không gửi kèm student_code, ta dọn sạch field này nếu ko phải sinh_vien
                if ($targetRole !== User::ROLE_SINH_VIEN) {
                    $updateData['student_code'] = null;
                }
            }

            if ($request->has('department_id')) {
                $updateData['department_id'] = $targetRole === User::ROLE_BO_MON ? $request->department_id : null;
            } elseif ($request->has('role')) {
                if ($targetRole !== User::ROLE_BO_MON) {
                    $updateData['department_id'] = null;
                }
            }

            $user->update($updateData);

        } catch (\Throwable $e) {
            // Alternative Flow 2: Chỉnh sửa thất bại
            Log::error('[UserController] Cập nhật người dùng thất bại', [
                'id'    => $user->id,
                'error' => $e->getMessage()
            ]);

            return $this->error(
                message: 'Chỉnh sửa thông tin người dùng thất bại. Vui lòng thử lại.',
                status: 500
            );
        }

        // ── Bước 5: Hiển thị thông tin người dùng đã chỉnh sửa
        return $this->success(
            data: $this->formatUser($user->fresh()),
            message: 'Thông tin người dùng đã được cập nhật',
        );
    }

    /**
     * DELETE /api/admin/users/{user}
     *
     * Use case : Xóa người dùng
     * Actor    : Admin
     *
     * Normal Flow:
     *   1. Admin chọn người dùng (GET /{id})                    ← bước 1
     *   2. Xác nhận xóa (client gửi DELETE request)             ← bước 2
     *   3. Xóa khỏi CSDL                                        ← bước 3
     *   4. Loại bỏ khỏi danh sách (không trả data, chỉ 200)     ← bước 4
     *
     * Alternative Flow:
     *   AF-1: Xóa thất bại         → 500
     *   AF-2: Admin hủy thao tác   → client không gọi API
     */
    #[OA\Delete(
        path: "/api/admin/users/{id}",
        operationId: "deleteUser",
        summary: "Xóa người dùng",
        description: "Xóa vĩnh viễn tài khoản người dùng khỏi hệ thống.",
        security: [["sanctum" => []]],
        tags: ["Người dùng"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200,  description: "Bước 4: Xóa thành công")]
    #[OA\Response(response: 403,  description: "Không có quyền – chỉ Admin, hoặc tự xóa mình")]
    #[OA\Response(response: 404,  description: "Không tìm thấy người dùng")]
    #[OA\Response(response: 500,  description: "AF-1: Xóa thất bại")]
    public function destroy(Request $request, User $user): JsonResponse
    {
        // ── Bước 1: Route model binding tự tìm, trả 404 nếu không có
        // ── Bước 2: Xác nhận xóa = Admin gửi DELETE request

        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        if ($request->user()->id === $user->id) {
            return $this->error('Không thể xóa tài khoản của chính mình.', null, 403);
        }

        // ── Bước 3: Xóa người dùng trong CSDL
        try {
            $user->delete();

        } catch (\Illuminate\Database\QueryException $e) {
            // Kiểm tra lỗi khóa ngoại (Constraint Violation) nếu user đã có liên kết dữ liệu
            if ($e->getCode() === '23000') {
                return $this->error(
                    message: 'Không thể xóa người dùng này vì đã có dữ liệu liên kết trong hệ thống.',
                    status: 409
                );
            }
            throw $e;
        } catch (\Throwable $e) {
            // Alternative Flow 1: xóa thất bại
            Log::error('[UserController] Xóa người dùng thất bại', [
                'id'    => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                message: 'Xóa người dùng thất bại. Vui lòng thử lại.',
                status: 500
            );
        }

        // ── Bước 4: Loại bỏ khỏi danh sách – trả về thành công
        return $this->success(
            data: null,
            message: "Đã xóa tài khoản '{$user->name}' thành công.",
        );
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function formatUser(object $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'role'          => $user->role,
            'student_code'  => $user->student_code,
            'department_id' => $user->department_id,
            'created_at'    => $user->created_at?->toIso8601String(),
        ];
    }
}
