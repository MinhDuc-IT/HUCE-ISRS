<?php

namespace App\Http\Controllers\Department;

use App\Application\Services\Department\DepartmentProfileService;
use App\Application\Services\Department\SendDepartmentSummaryEmailService;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Department\SendDepartmentSummaryEmailRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SummaryEmailController extends BaseController
{
    public function __construct(
        private readonly DepartmentProfileService $profileService,
        private readonly SendDepartmentSummaryEmailService $summaryEmailService,
    ) {}

    public function send(SendDepartmentSummaryEmailRequest $request): JsonResponse
    {
        try {
            $departmentId = $this->profileService->resolveDepartmentId($request->user());

            $this->summaryEmailService->send(
                $departmentId,
                $request->input('subject'),
                $request->input('body')
            );

            return $this->success(null, 'Đã gửi email tổng hợp về bộ môn thành công');
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'không tồn tại') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        } catch (\Throwable $e) {
            Log::error('[Department\SummaryEmailController] Gửi email thất bại: ' . $e->getMessage());

            return $this->error(
                'Gửi email thất bại. Vui lòng kiểm tra lại cấu hình mail server.',
                null,
                500
            );
        }
    }
}
