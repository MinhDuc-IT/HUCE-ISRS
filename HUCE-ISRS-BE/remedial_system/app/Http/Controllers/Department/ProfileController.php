<?php

namespace App\Http\Controllers\Department;

use App\Application\Services\Department\DepartmentProfileService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Department\UpdateDepartmentProfileRequest;
use Illuminate\Http\JsonResponse;

class ProfileController extends BaseController
{
    public function __construct(
        private readonly DepartmentProfileService $profileService,
    ) {}

    public function show(): JsonResponse
    {
        try {
            return $this->success(
                $this->profileService->getProfilePayload(request()->user())
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    public function update(UpdateDepartmentProfileRequest $request): JsonResponse
    {
        try {
            return $this->success(
                $this->profileService->updateProfile($request->user(), $request->validated()),
                'Cập nhật thông tin bộ môn thành công'
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }
}
