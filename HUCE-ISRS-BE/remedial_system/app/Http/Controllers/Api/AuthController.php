<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Application\Services\StudentProvisioningService;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\StudentNotFoundException;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Xác thực",
    description: "Đăng nhập và quản lý phiên làm việc"
)]
class AuthController extends BaseController
{
    public function __construct(
        private readonly StudentProvisioningService $provisioningService,
        private readonly \App\Domain\Ports\StudentInfoPort $studentInfoPort,
    ) {}

    /**
     * POST /api/auth/login
     *
     * Use case: Đăng nhập
     * Actor   : Admin | Bộ môn | Sinh viên
     *
     * Normal Flow:
     *   1. Người dùng gửi (email hoặc student_code) + password
     *   2. LoginRequest validate đầu vào                              ← bước 2
     *   3a. Admin/Bộ môn : tìm theo email trong local DB              ← bước 3
     *   3b. Sinh viên    : tìm theo student_code → nếu chưa có
     *                      → gọi University System xác minh
     *                      → auto-provision tài khoản (Option B)
     *   4. Trả token + home_url theo vai trò                          ← bước 4
     *
     * Alternative Flow:
     *   AF-1: Validation fail          → 422
     *   AF-2: Sai mật khẩu            → 401
     *   AF-3: Sinh viên không tồn tại trên University System → 404
     *   AF-4: University System lỗi   → 503
     */
    #[OA\Post(
        path: "/api/auth/login",
        operationId: "login",
        summary: "Đăng nhập vào hệ thống",
        description: "Admin/Bộ môn dùng email + password. Sinh viên dùng student_code + password (tự động tạo tài khoản lần đầu qua University System).",
        tags: ["Xác thực"],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email",        type: "string", format: "email",    nullable: true, example: "admin@remedial.edu.vn",
                    description: "Email – dùng cho Admin hoặc Bộ môn"),
                new OA\Property(property: "student_code", type: "string",                    nullable: true, example: "SV001",
                    description: "Mã sinh viên – dùng cho Sinh viên"),
                new OA\Property(property: "password",     type: "string", format: "password",              example: "nuce_backdoor_2026",
                    description: "Mật khẩu. Sinh viên đăng nhập lần đầu dùng mã sinh viên làm mật khẩu."),
            ]
        )
    )]
    #[OA\Response(response: 200,  description: "Đăng nhập thành công")]
    #[OA\Response(response: 401,  description: "AF-2: Sai mật khẩu")]
    #[OA\Response(response: 404,  description: "AF-3: Sinh viên không tồn tại trên University System")]
    #[OA\Response(response: 422,  description: "AF-1: Dữ liệu đầu vào không hợp lệ")]
    #[OA\Response(response: 503,  description: "AF-4: University System không khả dụng")]
    public function login(LoginRequest $request): JsonResponse
    {
        // ── Bước 2 đã xử lý bởi LoginRequest ──────────────────────────────────

        if ($request->filled('student_code')) {
            return $this->loginAsSinhVien($request);
        }

        return $this->loginAsStaff($request);
    }

    /**
     * POST /api/auth/logout
     */
    #[OA\Post(
        path: "/api/auth/logout",
        operationId: "logout",
        summary: "Đăng xuất",
        security: [["sanctum" => []]],
        tags: ["Xác thực"],
    )]
    #[OA\Response(response: 200, description: "Đăng xuất thành công")]
    #[OA\Response(response: 401, description: "Chưa xác thực")]
    public function logout(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return $this->success(message: 'Đăng xuất thành công');
    }

    /**
     * GET /api/auth/me
     */
    #[OA\Get(
        path: "/api/auth/me",
        operationId: "me",
        summary: "Thông tin người dùng đang đăng nhập",
        security: [["sanctum" => []]],
        tags: ["Xác thực"],
    )]
    #[OA\Response(response: 200, description: "Thông tin tài khoản")]
    #[OA\Response(response: 401, description: "Chưa xác thực")]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return $this->success($this->buildUserPayload($user));
    }

    // =========================================================================
    // Private – Login strategies
    // =========================================================================

    /**
     * Option B – Sinh viên đăng nhập bằng student_code.
     *
     * Bước 3b Normal Flow:
     *   1. Tìm user local theo student_code.
     *   2. Nếu không có → gọi University System xác minh → auto-provision.
     *   3. Kiểm tra password.
     *   4. Nếu đang dùng mật khẩu mặc định → cảnh báo yêu cầu đổi mật khẩu.
     */
    private function loginAsSinhVien(LoginRequest $request): JsonResponse
    {
        $studentCode = strtoupper(trim($request->student_code));

        try {
            // ── Bước 1: Xác thực thông tin đăng nhập với University System ─────
            $isValid = $this->studentInfoPort->verifyCredentials($studentCode, $request->password);

            if (! $isValid) {
                return $this->error(
                    message: 'Mã sinh viên hoặc mật khẩu không chính xác (xác thực từ trường).',
                    status: 401
                );
            }

            // ── Bước 2: Sau khi xác thực OK, tìm hoặc đồng bộ tài khoản local ──
            $user = $this->provisioningService->findOrProvision($studentCode);

        } catch (StudentNotFoundException $e) {
            // AF-1: Sinh viên không tồn tại
            return $this->error(
                message: "Mã sinh viên '{$studentCode}' không tồn tại trong hệ thống trường.",
                status: 404
            );
        } catch (ExternalSystemException $e) {
            // AF-4: University System không khả dụng
            Log::error('[AuthController] University System lỗi khi login', [
                'student_code' => $studentCode,
                'error'        => $e->getMessage(),
            ]);
            return $this->error(
                message: 'Không thể xác minh với hệ thống trường. Vui lòng thử lại sau.',
                status: 503
            );
        }

        // Cảnh báo nếu vẫn dùng mật khẩu mặc định (= student_code)
        // Lưu ý: Lúc này $user đã được tạo/cập nhật mật khẩu đồng bộ

        // Cảnh báo nếu vẫn dùng mật khẩu mặc định (= student_code)
        $isFirstLogin = $this->provisioningService->isFirstLogin($user, $studentCode);

        return $this->issueToken($user, [
            'first_login'         => $isFirstLogin,
            'first_login_warning' => $isFirstLogin
                ? 'Bạn đang dùng mật khẩu mặc định. Vui lòng đổi mật khẩu ngay.'
                : null,
        ]);
    }

    /**
     * Admin / Bộ môn đăng nhập bằng email.
     *
     * Bước 3a Normal Flow: tra cứu trong local DB, không gọi University System.
     */
    private function loginAsStaff(LoginRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = User::where('email', $request->email)
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_BO_MON])
            ->first();

        // AF-2: tài khoản không tồn tại hoặc sai mật khẩu
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error(
                message: 'Email hoặc mật khẩu không chính xác.',
                status: 401
            );
        }

        return $this->issueToken($user);
    }

    // =========================================================================
    // Private – Helpers
    // =========================================================================

    /**
     * Tạo Sanctum token và trả JsonResponse chuẩn.
     *
     * Bước 4 Normal Flow: chuyển đến trang chủ phụ thuộc vào Actor.
     *
     * @param array $extra Dữ liệu thêm vào payload (VD: first_login warning)
     */
    private function issueToken(User $user, array $extra = []): JsonResponse
    {
        // Xóa token cũ để mỗi lúc chỉ có 1 session active
        $user->tokens()->delete();

        $token = $user->createToken(
            name:      "login:{$user->role}",
            abilities: $this->resolveAbilities($user->role),
        )->plainTextToken;

        return $this->success(
            data: array_merge(
                [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'user'       => $this->buildUserPayload($user),
                ],
                $extra
            ),
            message: 'Đăng nhập thành công',
        );
    }

    /**
     * Payload thông tin người dùng trả về client.
     */
    private function buildUserPayload(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'role'          => $user->role,
            'student_code'  => $user->student_code,
            'department_id' => $user->department_id,
            'home_url'      => $this->resolveHomeUrl($user->role),
        ];
    }

    /**
     * Token abilities theo vai trò – dùng để phân quyền API.
     *
     * @return string[]
     */
    private function resolveAbilities(string $role): array
    {
        return match ($role) {
            User::ROLE_ADMIN     => ['*'],
            User::ROLE_BO_MON    => ['registrations:read', 'registrations:approve'],
            User::ROLE_SINH_VIEN => ['registrations:read', 'registrations:create', 'registrations:cancel'],
            default              => [],
        };
    }

    /**
     * Bước 4 Normal Flow: URL trang chủ phụ thuộc vào Actor.
     */
    private function resolveHomeUrl(string $role): string
    {
        return match ($role) {
            User::ROLE_ADMIN     => '/admin/dashboard',
            User::ROLE_BO_MON    => '/bo-mon/registrations',
            User::ROLE_SINH_VIEN => '/sinh-vien/registrations',
            default              => '/',
        };
    }
}
