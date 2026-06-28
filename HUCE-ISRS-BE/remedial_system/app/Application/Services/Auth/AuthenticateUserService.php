<?php

namespace App\Application\Services\Auth;

use App\Application\Services\StudentProvisioningService;
use App\Domain\Exceptions\AccountDeactivatedException;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Domain\Ports\External\StudentInfoPort;
use App\Domain\Ports\Persistence\UserRepositoryPort;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final class AuthenticateUserService
{
    public function __construct(
        private readonly UserRepositoryPort $userRepository,
        private readonly StudentInfoPort $studentInfoPort,
        private readonly StudentProvisioningService $provisioningService,
        private readonly AuthUserPresenter $presenter,
    ) {}

    /**
     * @return array{token: string, token_type: string, user: array<string, mixed>}
     */
    public function loginStaff(string $email, string $password): array
    {
        if ($this->userRepository->isStaffEmailDeactivated($email)) {
            throw new AccountDeactivatedException('Tài khoản đã bị vô hiệu hóa.');
        }

        $user = $this->userRepository->findStaffByEmail($email);

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException('Email hoặc mật khẩu không chính xác.');
        }

        // Nếu là tài khoản bộ môn, tiến hành đồng bộ giảng viên của bộ môn đó (bất đồng bộ/đồng bộ nhẹ)
        try {
            if ($user->role === User::ROLE_BO_MON && $user->department_id) {
                \App\Jobs\SyncDepartmentLecturersJob::dispatch((int) $user->department_id);
            }
        } catch (\Exception $e) {
            Log::error('[AuthenticateUserService] Enqueue Teacher sync job failed during bo_mon login', ['error' => $e->getMessage()]);
        }

        return $this->createSession($user);
    }

    /**
     * @return array{
     *   token: string,
     *   token_type: string,
     *   user: array<string, mixed>,
     *   first_login: bool,
     *   first_login_warning: string|null
     * }
     */
    public function loginStudent(string $studentCode, string $password): array
    {
        $studentCode = strtoupper(trim($studentCode));

        if ($this->userRepository->isStudentCodeDeactivated($studentCode)) {
            throw new AccountDeactivatedException('Tài khoản sinh viên đã bị vô hiệu hóa.');
        }

        if (! $this->studentInfoPort->verifyCredentials($studentCode, $password)) {
            throw new InvalidCredentialsException(
                'Mã sinh viên hoặc mật khẩu không chính xác (xác thực từ trường).'
            );
        }

        try {
            $user = $this->provisioningService->findOrProvision($studentCode);
        } catch (StudentNotFoundException $e) {
            throw $e;
        } catch (ExternalSystemException $e) {
            throw $e;
        }

        $isFirstLogin = $this->provisioningService->isFirstLogin($user, $studentCode);

        return array_merge(
            $this->createSession($user),
            [
                'first_login'         => $isFirstLogin,
                'first_login_warning' => $isFirstLogin
                    ? 'Bạn đang dùng mật khẩu mặc định. Vui lòng đổi mật khẩu ngay.'
                    : null,
            ]
        );
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /** @return array<string, mixed> */
    public function currentUserPayload(User $user): array
    {
        if ($user->is_deleted) {
            throw new AccountDeactivatedException('Tài khoản đã bị vô hiệu hóa.');
        }

        return $this->presenter->present($user);
    }

    /**
     * @return array{token: string, token_type: string, user: array<string, mixed>}
     */
    private function createSession(User $user): array
    {
        $user->tokens()->delete();

        $token = $user->createToken("login:{$user->role}")->plainTextToken;

        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $this->presenter->present($user),
        ];
    }
}
