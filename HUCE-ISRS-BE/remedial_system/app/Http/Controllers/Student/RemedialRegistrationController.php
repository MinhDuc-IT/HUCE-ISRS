<?php

namespace App\Http\Controllers\Student;

use App\Application\Services\RemedialRegistrationService;
use App\Application\Services\StudentRegistrationPresenter;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Student\StoreRemedialRegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Sinh viên – Đăng ký phụ đạo', description: 'Đăng ký / hủy / xem đơn của chính sinh viên')]
class RemedialRegistrationController extends BaseController
{
    public function __construct(
        private readonly RemedialRegistrationService $registrationService,
        private readonly StudentRegistrationPresenter $presenter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $remedialTermId = $request->filled('remedial_term_id')
                ? $request->integer('remedial_term_id')
                : null;
            $registrations = $this->registrationService->getRegistrationsForUser($user, $remedialTermId);

            return $this->success($this->presenter->formatMany($registrations, $user));
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    public function store(StoreRemedialRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user      = $request->user();
        $periods   = $validated['remedial_periods'] ?? null;

        try {
            if (isset($validated['course_codes'])) {
                $registrations = $this->registrationService->bulkRegisterForUser(
                    $user,
                    $validated['course_codes']
                );
                $data    = $this->presenter->formatMany($registrations, $user);
                $message = 'Đăng ký các học phần phụ đạo thành công';
            } else {
                $registration = $this->registrationService->registerForUser(
                    $user,
                    $validated['course_code'],
                    $periods
                );
                $data    = $this->presenter->format($registration, $user);
                $message = 'Đăng ký học phụ đạo thành công';
            }

            return $this->success($data, $message, 201);
        } catch (StudentNotFoundException $e) {
            return $this->error($e->getMessage(), null, 404);
        } catch (ExternalSystemException $e) {
            return $this->error($e->getMessage(), null, 503);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $this->registrationService->cancelRegistrationForUser($user, $id);

            $remedialTermId = $request->filled('remedial_term_id')
                ? $request->integer('remedial_term_id')
                : null;
            $registrations = $this->registrationService->getRegistrationsForUser($user, $remedialTermId);

            return $this->success(
                $this->presenter->formatMany($registrations, $user),
                'Hủy đăng ký thành công'
            );
        } catch (\DomainException $e) {
            $status = str_contains($e->getMessage(), 'Không tìm thấy') ? 404 : 400;

            return $this->error($e->getMessage(), null, $status);
        }
    }
}
