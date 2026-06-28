<?php

namespace App\Http\Controllers\Student;

use App\Application\Services\RemedialRegistrationService;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Sinh viên – Môn đăng ký chính quy theo đợt', description: 'Môn SV đã đăng ký học chính quy trùng kỳ/năm đợt phụ đạo hiện tại')]
class TermRegisteredSubjectController extends BaseController
{
    public function __construct(
        private readonly RemedialRegistrationService $registrationService,
    ) {}

    public function index(): JsonResponse
    {
        try {
            $courses = $this->registrationService->getTermRegisteredSubjectsForUser(request()->user());

            $data = array_map(fn ($c) => [
                'course_code'              => $c->code(),
                'subject_name'             => $c->subjectName,
                'credits'                  => $c->credits,
                'class_section_code'       => $c->classSectionCode,
                'lop_du_kien'              => $c->plannedClass,
                'registration_date'        => $c->registrationDate,
                'registration_status'      => $c->registrationStatusName,
                'academic_year_label'      => $c->academicYearLabel,
                'academic_year'            => $c->academicYear,
                'semester'                 => $c->semesterOrder,
                'term_name'                => $c->termName,
                'exam_date'                => $c->examDate,
            ], $courses);

            return $this->success($data, 'Danh sách môn đã đăng ký học chính quy theo đợt phụ đạo');
        } catch (StudentNotFoundException $e) {
            return $this->error($e->getMessage(), null, 404);
        } catch (ExternalSystemException $e) {
            return $this->error($e->getMessage(), null, 503);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }
}
