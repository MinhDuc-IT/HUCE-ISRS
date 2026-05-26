<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\Auth\AuthenticateUserService;
use App\Domain\Exceptions\AccountDeactivatedException;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Xác thực',
    description: 'Đăng nhập và quản lý phiên làm việc'
)]
class AuthController extends BaseController
{
    public function __construct(
        private readonly AuthenticateUserService $authenticateUserService,
    ) {}

    #[OA\Post(
        path: '/api/auth/login',
        operationId: 'login',
        summary: 'Đăng nhập vào hệ thống',
        description: 'Admin/Bộ môn dùng email + password. Sinh viên dùng student_code + password (tự động tạo tài khoản lần đầu qua University System).',
        tags: ['Xác thực'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'admin@remedial.edu.vn'),
                new OA\Property(property: 'student_code', type: 'string', nullable: true, example: 'SV001'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'nuce_backdoor_2026'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Đăng nhập thành công')]
    #[OA\Response(response: 401, description: 'Sai thông tin đăng nhập')]
    #[OA\Response(response: 403, description: 'Tài khoản đã bị vô hiệu hóa')]
    #[OA\Response(response: 404, description: 'Sinh viên không tồn tại trên University System')]
    #[OA\Response(response: 422, description: 'Dữ liệu đầu vào không hợp lệ')]
    #[OA\Response(response: 503, description: 'University System không khả dụng')]
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $request->filled('student_code')
                ? $this->authenticateUserService->loginStudent(
                    $request->string('student_code')->toString(),
                    $request->string('password')->toString(),
                )
                : $this->authenticateUserService->loginStaff(
                    $request->string('email')->toString(),
                    $request->string('password')->toString(),
                );

            return $this->success($data, 'Đăng nhập thành công');
        } catch (InvalidCredentialsException $e) {
            return $this->error($e->getMessage(), null, 401);
        } catch (AccountDeactivatedException $e) {
            return $this->error($e->getMessage(), null, 403);
        } catch (StudentNotFoundException $e) {
            return $this->error($e->getMessage(), null, 404);
        } catch (ExternalSystemException $e) {
            Log::error('[AuthController] University System lỗi khi login', [
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                'Không thể xác minh với hệ thống trường. Vui lòng thử lại sau.',
                null,
                503
            );
        }
    }

    #[OA\Post(
        path: '/api/auth/logout',
        operationId: 'logout',
        summary: 'Đăng xuất',
        security: [['sanctum' => []]],
        tags: ['Xác thực'],
    )]
    #[OA\Response(response: 200, description: 'Đăng xuất thành công')]
    public function logout(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authenticateUserService->logout($user);

        return $this->success(message: 'Đăng xuất thành công');
    }

    #[OA\Get(
        path: '/api/auth/me',
        operationId: 'me',
        summary: 'Thông tin người dùng đang đăng nhập',
        security: [['sanctum' => []]],
        tags: ['Xác thực'],
    )]
    #[OA\Response(response: 200, description: 'Thông tin tài khoản')]
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user === null) {
            return $this->error('Chưa xác thực. Vui lòng đăng nhập.', null, 401);
        }

        try {
            return $this->success($this->authenticateUserService->currentUserPayload($user));
        } catch (AccountDeactivatedException $e) {
            $user->currentAccessToken()?->delete();

            return $this->error($e->getMessage(), null, 403);
        }
    }
}
