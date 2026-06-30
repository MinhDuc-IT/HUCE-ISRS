<?php

namespace Tests\Feature;

use App\Domain\Enums\RemedialTermStatus;
use App\Models\Department;
use App\Models\RemedialRegistration;
use App\Models\RemedialTerm;
use App\Models\Subject;
use Carbon\Carbon;
use Tests\TestCase;

class AdminRegistrationTest extends TestCase
{
    public function test_admin_remedial_registrations_are_grouped_by_term_and_subject(): void
    {
        $department = Department::where('department_code', '54')->firstOrFail();

        $termA = RemedialTerm::create([
            'name'                 => 'Đợt A',
            'year'                 => 2024,
            'semester'             => 1,
            'start_date'           => Carbon::now()->subMonths(2),
            'end_date'             => Carbon::now()->addMonth(),
            'registration_start'   => Carbon::now()->subMonths(2),
            'registration_end'     => Carbon::now()->addMonth(),
            'remedial_coefficient' => 1,
            'price_per_period'     => 150000,
            'price_coefficient'    => 1,
            'status'               => RemedialTermStatus::REGISTRATION_OPEN,
            'is_deleted'           => false,
        ]);

        $termB = RemedialTerm::create([
            'name'                 => 'Đợt B',
            'year'                 => 2025,
            'semester'             => 1,
            'start_date'           => Carbon::now()->subMonth(),
            'end_date'             => Carbon::now()->addMonths(2),
            'registration_start'   => Carbon::now()->subMonth(),
            'registration_end'     => Carbon::now()->addMonths(2),
            'remedial_coefficient' => 1,
            'price_per_period'     => 150000,
            'price_coefficient'    => 1,
            'status'               => RemedialTermStatus::REGISTRATION_OPEN,
            'is_deleted'           => false,
        ]);

        $subject = Subject::create([
            'subject_code'  => 'ADMGROUP01',
            'name'          => 'Môn group admin',
            'credits'       => 3,
            'department_id' => $department->id,
            'is_deleted'    => false,
        ]);

        $studentOne = $this->createStudentUser('SVADM01', 'SVADM01');
        $studentTwo = $this->createStudentUser('SVADM02', 'SVADM02');

        RemedialRegistration::create([
            'student_id'        => $studentOne->id,
            'subject_id'        => $subject->id,
            'remedial_term_id'  => $termA->id,
            'remedial_periods'  => 45,
            'registration_date' => Carbon::now(),
            'lecture_name'      => 'GV A',
            'is_deleted'        => false,
        ]);

        RemedialRegistration::create([
            'student_id'        => $studentTwo->id,
            'subject_id'        => $subject->id,
            'remedial_term_id'  => $termA->id,
            'remedial_periods'  => 45,
            'registration_date' => Carbon::now(),
            'lecture_name'      => 'GV A',
            'is_deleted'        => false,
        ]);

        RemedialRegistration::create([
            'student_id'        => $studentOne->id,
            'subject_id'        => $subject->id,
            'remedial_term_id'  => $termB->id,
            'remedial_periods'  => 45,
            'registration_date' => Carbon::now(),
            'is_deleted'        => false,
        ]);

        $response = $this->actingAsAdmin()
            ->apiJson('GET', '/admin/remedial-registrations')
            ->assertOk();

        $rows = collect($response->json('data'));

        $this->assertCount(2, $rows);

        $termARow = $rows->first(fn ($row) => $row['remedial_term_id'] === $termA->id);
        $termBRow = $rows->first(fn ($row) => $row['remedial_term_id'] === $termB->id);

        $this->assertNotNull($termARow);
        $this->assertSame('ADMGROUP01', $termARow['subject_code']);
        $this->assertSame(2, $termARow['student_count']);
        $this->assertSame('GV A', $termARow['lecture_name']);

        $this->assertNotNull($termBRow);
        $this->assertSame(1, $termBRow['student_count']);
        $this->assertNull($termBRow['lecture_name']);
    }

    public function test_admin_can_list_students_for_term_and_subject_group(): void
    {
        $department = Department::where('department_code', '54')->firstOrFail();

        $term = RemedialTerm::create([
            'name'                 => 'Đợt chi tiết',
            'year'                 => 2024,
            'semester'             => 1,
            'start_date'           => Carbon::now()->subMonths(2),
            'end_date'             => Carbon::now()->addMonth(),
            'registration_start'   => Carbon::now()->subMonths(2),
            'registration_end'     => Carbon::now()->addMonth(),
            'remedial_coefficient' => 1,
            'price_per_period'     => 150000,
            'price_coefficient'    => 1,
            'status'               => RemedialTermStatus::REGISTRATION_OPEN,
            'is_deleted'           => false,
        ]);

        $subject = Subject::create([
            'subject_code'  => 'ADMDETAIL01',
            'name'          => 'Môn chi tiết admin',
            'credits'       => 3,
            'department_id' => $department->id,
            'is_deleted'    => false,
        ]);

        $studentOne = $this->createStudentUser('SVADMD01', 'SVADMD01');
        $studentTwo = $this->createStudentUser('SVADMD02', 'SVADMD02');

        $this->fakeStudentInfo->registerStudent(
            'SVADMD01',
            'SVADMD01',
            new \App\Domain\Entities\StudentInfo(
                id: 'SVADMD01',
                fullName: 'Sinh viên A',
                gender: 'Nam',
                dateOfBirth: '2000-01-01',
                placeOfBirth: 'Huế',
                personalEmail: 'svadmd01@test.edu.vn',
                universityEmail: null,
                gpaScale10: 5.0,
                gpaScale4: 2.0,
                gradeClassification: 'Trung bình',
                totalCredits: 100,
                failedCredits: 6,
            ),
            termRegisteredCourses: [
                new \App\Domain\Entities\TermRegisteredCourse(
                    subjectCode: 'ADMDETAIL01',
                    subjectName: 'Môn chi tiết admin',
                    credits: 3,
                    classSectionCode: 'ADMDETAIL01-01',
                    plannedClass: 'CNTT01',
                    registrationDate: '2024-01-10',
                    registrationId: 1,
                    registrationStatusId: 1,
                    registrationStatusName: 'Đã đăng ký',
                    academicYearLabel: '2024-2025',
                    academicYear: 2024,
                    semesterOrder: 1,
                    termName: 'HK1',
                ),
            ],
            termYear: 2024,
            termSemester: 1,
        );

        RemedialRegistration::create([
            'student_id'        => $studentOne->id,
            'subject_id'        => $subject->id,
            'remedial_term_id'  => $term->id,
            'remedial_periods'  => 45,
            'registration_date' => Carbon::parse('2024-03-01'),
            'is_deleted'        => false,
        ]);

        RemedialRegistration::create([
            'student_id'        => $studentTwo->id,
            'subject_id'        => $subject->id,
            'remedial_term_id'  => $term->id,
            'remedial_periods'  => 45,
            'registration_date' => Carbon::parse('2024-03-02'),
            'is_deleted'        => false,
        ]);

        $this->actingAsAdmin()
            ->apiJson('GET', "/admin/remedial-registrations/students?remedial_term_id={$term->id}&subject_id={$subject->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'student_code' => 'SVADMD01',
                'class_name'   => 'CNTT01',
            ])
            ->assertJsonFragment([
                'student_code' => 'SVADMD02',
                'class_name'   => null,
            ]);
    }
}
