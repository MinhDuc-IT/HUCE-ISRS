<?php

namespace App\Http\Controllers\Department;

use App\Application\Services\Department\DepartmentProfileService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Department\UpdateDepartmentProfileRequest;
use App\Http\Resources\DepartmentResource;
use Illuminate\Http\JsonResponse;

class ProfileController extends BaseController
{
    public function __construct(
        private readonly DepartmentProfileService $profileService,
    ) {}

    public function show(): JsonResponse
    {
        try {
            $dept = $this->profileService->getProfile(request()->user());

            return $this->success((new DepartmentResource($dept))->resolve());
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    public function update(UpdateDepartmentProfileRequest $request): JsonResponse
    {
        try {
            $dept = $this->profileService->updateProfile($request->user(), $request->validated());

            return $this->success(
                (new DepartmentResource($dept))->resolve(),
                'Cập nhật thông tin bộ môn thành công'
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }
}
