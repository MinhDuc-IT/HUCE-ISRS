<?php

namespace App\Http\Controllers\Admin;

use App\Application\Services\Admin\ManageUserService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Người dùng', description: 'Quản lý người dùng hệ thống – chỉ Admin')]
class UserController extends BaseController
{
    public function __construct(
        private readonly ManageUserService $userService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->list($request->query('role'));

        return $this->success(UserResource::collection($users)->resolve());
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->create($request->validated());

            return $this->success(
                (new UserResource($user))->resolve(),
                'Thêm người dùng thành công',
                201
            );
        } catch (\Throwable $e) {
            Log::error('[Admin\UserController] Thêm người dùng thất bại', ['error' => $e->getMessage()]);

            return $this->error('Thêm người dùng thất bại. Vui lòng thử lại.', null, 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if ($user === null) {
            return $this->error("Không tìm thấy người dùng #{$id}.", null, 404);
        }

        return $this->success((new UserResource($user))->resolve());
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $updated = $this->userService->update($user, $request->validated());

            return $this->success(
                (new UserResource($updated))->resolve(),
                'Thông tin người dùng đã được cập nhật'
            );
        } catch (\Throwable $e) {
            Log::error('[Admin\UserController] Cập nhật thất bại', ['id' => $user->id, 'error' => $e->getMessage()]);

            return $this->error('Chỉnh sửa thông tin người dùng thất bại. Vui lòng thử lại.', null, 500);
        }
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        try {
            $this->userService->delete($user->id, $request->user()->id);

            return $this->success(null, "Đã xóa tài khoản '{$user->name}' thành công.");
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'liên kết') ? 409 : (
                str_contains($e->getMessage(), 'chính mình') ? 403 : 404
            );

            return $this->error($e->getMessage(), null, $status);
        } catch (\Throwable $e) {
            Log::error('[Admin\UserController] Xóa thất bại', ['id' => $user->id, 'error' => $e->getMessage()]);

            return $this->error('Xóa người dùng thất bại. Vui lòng thử lại.', null, 500);
        }
    }
}
