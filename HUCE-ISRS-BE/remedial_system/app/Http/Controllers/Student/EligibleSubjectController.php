<?php

namespace App\Http\Controllers\Student;

use App\Application\Services\RemedialRegistrationService;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Sinh viên – Môn đủ điều kiện', description: 'Môn học được phép đăng ký phụ đạo')]
class EligibleSubjectController extends BaseController
{
    public function __construct(
        private readonly RemedialRegistrationService $registrationService,
    ) {}

    public function index(): JsonResponse
    {
        try {
            $courses = $this->registrationService->getEligibleSubjectsForUser(request()->user());

            $data = array_map(fn ($c) => [
                'course_code'  => $c->code(),
                'subject_name' => $c->subjectName,
                'credits'      => $c->credits,
                'final_score'  => $c->finalScore,
                'letter_grade' => $c->letterGrade,
            ], $courses);

            return $this->success($data, 'Danh sách môn đủ điều kiện học phụ đạo');
        } catch (StudentNotFoundException $e) {
            return $this->error($e->getMessage(), null, 404);
        } catch (ExternalSystemException $e) {
            return $this->error($e->getMessage(), null, 503);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }
}
