<?php

namespace Tests\Feature;

use App\Domain\Entities\StudentInfo;
use App\Domain\Entities\TermRegisteredCourse;
use App\Models\Department;
use App\Models\RemedialTerm;
use App\Models\Subject;
use App\Domain\Enums\RemedialTermStatus;
use Carbon\Carbon;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    private function seedOpenTermAndSubject(): array
    {
        $department = Department::where('department_code', '54')->firstOrFail();

        $term = RemedialTerm::create([
            'name'                 => 'Đợt test đăng ký',
            'year'                 => 2025,
            'semester'             => 1,
            'start_date'           => Carbon::now()->subDays(10),
            'end_date'             => Carbon::now()->addMonths(3),
            'registration_start'   => Carbon::now()->subDays(2),
            'registration_end'     => Carbon::now()->addDays(10),
            'remedial_coefficient' => 1,
            'price_per_period'     => 150000,
            'price_coefficient'    => 1,
            'status'               => RemedialTermStatus::REGISTRATION_OPEN,
            'is_deleted'           => false,
        ]);

        $subject = Subject::create([
            'subject_code'  => 'CS101',
            'name'          => 'Lập trình căn bản',
            'credits'       => 3,
            'department_id' => $department->id,
            'is_deleted'    => false,
        ]);

        return [$term, $subject];
    }

    public function test_student_can_register_and_list_remedial_registration(): void
    {
        [$term] = $this->seedOpenTermAndSubject();
        $student = $this->createStudentUser('SVTEST', 'SVTEST');

        $register = $this->actingAs($student, 'sanctum')
            ->apiJson('POST', '/student/me/remedial-registrations', [
                'course_code' => 'CS101',
            ]);

        $register->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.course_code', 'CS101');

        $this->actingAs($student, 'sanctum')
            ->apiJson('GET', '/student/me/remedial-registrations?remedial_term_id='.$term->id)
            ->assertOk()
            ->assertJsonFragment(['course_code' => 'CS101']);

        $this->assertDatabaseHas('remedial_registrations', [
            'student_id' => $student->id,
            'is_deleted' => false,
        ]);
    }

    public function test_student_can_register_on_last_day_of_registration_window(): void
    {
        $department = Department::where('department_code', '54')->firstOrFail();

        RemedialTerm::create([
            'name'                 => 'Đợt đóng hôm nay',
            'year'                 => 2025,
            'semester'             => 1,
            'start_date'           => Carbon::now()->subDays(10),
            'end_date'             => Carbon::now()->addMonths(3),
            'registration_start'   => Carbon::now()->subDays(5)->startOfDay(),
            'registration_end'     => Carbon::now()->toDateString(),
            'remedial_coefficient' => 1,
            'price_per_period'     => 150000,
            'price_coefficient'    => 1,
            'status'               => RemedialTermStatus::REGISTRATION_OPEN,
            'is_deleted'           => false,
        ]);

        Subject::create([
            'subject_code'  => 'CS101',
            'name'          => 'Lập trình căn bản',
            'credits'       => 3,
            'department_id' => $department->id,
            'is_deleted'    => false,
        ]);

        $student = $this->createStudentUser('SVTODAY', 'SVTODAY');

        $this->actingAs($student, 'sanctum')
            ->apiJson('POST', '/student/me/remedial-registrations', [
                'course_codes' => ['CS101'],
            ])
            ->assertCreated();
    }

    public function test_student_can_cancel_registration(): void
    {
        $this->seedOpenTermAndSubject();
        $student = $this->createStudentUser('SVCANCEL', 'SVCANCEL');

        $created = $this->actingAs($student, 'sanctum')
            ->apiJson('POST', '/student/me/remedial-registrations', [
                'course_code' => 'CS101',
            ]);

        $registrationId = $created->json('data.id');

        $this->actingAs($student, 'sanctum')
            ->apiJson('DELETE', "/student/me/remedial-registrations/{$registrationId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('remedial_registrations', [
            'id'         => $registrationId,
            'is_deleted' => true,
        ]);
    }

    public function test_student_term_registered_subjects_returns_courses_for_current_term(): void
    {
        $this->seedOpenTermAndSubject();
        $student = $this->createStudentUser('SVTERM', 'SVTERM');

        $response = $this->actingAs($student, 'sanctum')
            ->apiJson('GET', '/student/me/term-registered-subjects');

        $response->assertOk()
            ->assertJsonFragment(['course_code' => 'CS101'])
            ->assertJsonFragment(['subject_name' => 'Lập trình căn bản'])
            ->assertJsonFragment(['lop_du_kien' => 'CNTT01']);
    }

    public function test_student_term_registered_subjects_excludes_courses_outside_exam_window(): void
    {
        $department = Department::where('department_code', '54')->firstOrFail();

        RemedialTerm::create([
            'name'                 => 'Đợt block 2',
            'year'                 => 2025,
            'semester'             => 1,
            'start_date'           => Carbon::now()->subDays(10),
            'end_date'             => Carbon::now()->addMonths(3),
            'registration_start'   => Carbon::now()->subDays(2),
            'registration_end'     => Carbon::now()->addDays(10),
            'remedial_coefficient' => 1,
            'price_per_period'     => 150000,
            'price_coefficient'    => 1,
            'status'               => RemedialTermStatus::REGISTRATION_OPEN,
            'is_deleted'           => false,
        ]);

        Subject::create([
            'subject_code'  => 'CS101',
            'name'          => 'Lập trình căn bản',
            'credits'       => 3,
            'department_id' => $department->id,
            'is_deleted'    => false,
        ]);

        $student = $this->createStudentUser('SVBLOCK', 'SVBLOCK');

        $this->fakeStudentInfo->registerStudent(
            'SVBLOCK',
            'SVBLOCK',
            new StudentInfo(
                id: 'SVBLOCK',
                fullName: 'Sinh viên Test',
                gender: 'Nam',
                dateOfBirth: '2000-01-01',
                placeOfBirth: 'Huế',
                personalEmail: 'svblock@test.edu.vn',
                universityEmail: null,
                gpaScale10: 5.0,
                gpaScale4: 2.0,
                gradeClassification: 'Trung bình',
                totalCredits: 100,
                failedCredits: 6,
            ),
            [],
            [
                new TermRegisteredCourse(
                    subjectCode: 'CS101',
                    subjectName: 'Lập trình căn bản',
                    credits: 3,
                    classSectionCode: 'CS101-01',
                    plannedClass: 'CNTT01',
                    registrationDate: '2025-09-01',
                    registrationId: 1,
                    registrationStatusId: 2,
                    registrationStatusName: 'Đã duyệt',
                    academicYearLabel: '2025-2026',
                    academicYear: 2025,
                    semesterOrder: 1,
                    termName: 'Học kỳ 1',
                    examDate: Carbon::now()->addMonths(4)->toDateString(),
                ),
            ],
            2025,
            1,
        );

        $this->actingAs($student, 'sanctum')
            ->apiJson('GET', '/student/me/term-registered-subjects')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_student_eligible_subjects_returns_failed_courses(): void
    {
        $this->seedOpenTermAndSubject();
        $student = $this->createStudentUser('SVELIG', 'SVELIG');

        $response = $this->actingAs($student, 'sanctum')
            ->apiJson('GET', '/student/me/eligible-subjects');

        $response->assertOk()
            ->assertJsonFragment(['course_code' => 'CS101']);
    }
}
