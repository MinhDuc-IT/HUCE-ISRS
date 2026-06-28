<?php

namespace Tests\Feature;

use App\Domain\Enums\RemedialTermStatus;
use App\Models\Department;
use App\Models\RemedialRegistration;
use App\Models\RemedialTerm;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class DepartmentRegistrationTest extends TestCase
{
    private function seedTermSubjectRegistration(
        Department $department,
        Carbon $registrationEnd,
        bool $isCurrent = false,
    ): array {
        $term = RemedialTerm::create([
            'name'                 => 'Đợt BM test',
            'year'                 => 2025,
            'semester'             => 1,
            'start_date'           => Carbon::now()->subDays(10),
            'end_date'             => Carbon::now()->addMonths(3),
            'registration_start'   => Carbon::now()->subDays(10)->startOfDay(),
            'registration_end'     => $registrationEnd,
            'remedial_coefficient' => 1,
            'price_per_period'     => 150000,
            'price_coefficient'    => 1,
            'status'               => RemedialTermStatus::REGISTRATION_OPEN,
            'is_current_term'      => $isCurrent,
            'is_deleted'           => false,
        ]);

        $subject = Subject::create([
            'subject_code'  => 'BMTEST01',
            'name'          => 'Môn thuộc bộ môn',
            'credits'       => 3,
            'department_id' => $department->id,
            'is_deleted'    => false,
        ]);

        $student = $this->createStudentUser('SVBM01', 'SVBM01');

        RemedialRegistration::create([
            'student_id'        => $student->id,
            'subject_id'        => $subject->id,
            'remedial_term_id'  => $term->id,
            'remedial_periods'  => 45,
            'registration_date' => Carbon::now(),
            'is_deleted'        => false,
        ]);

        return [$term, $subject, $student];
    }

    public function test_department_subject_assignments_lists_grouped_by_subject(): void
    {
        $department = Department::where('department_code', '54')->firstOrFail();
        $boMon = User::where('email', 'bokhoa.cntt@remedial.edu.vn')->firstOrFail();

        $this->seedTermSubjectRegistration(
            $department,
            Carbon::now()->subDay()->endOfDay(),
        );

        $this->actingAs($boMon, 'sanctum')
            ->apiJson('GET', '/department/subject-assignments')
            ->assertOk()
            ->assertJsonFragment(['subject_code' => 'BMTEST01'])
            ->assertJsonFragment(['registration_count' => 1])
            ->assertJsonPath('data.0.can_assign_lecturer', true);
    }

    public function test_department_can_bulk_assign_lecturer_after_registration_end(): void
    {
        $department = Department::where('department_code', '54')->firstOrFail();
        $boMon = User::where('email', 'bokhoa.cntt@remedial.edu.vn')->firstOrFail();

        [, $subject] = $this->seedTermSubjectRegistration(
            $department,
            Carbon::now()->subDay()->endOfDay(),
        );

        $this->actingAs($boMon, 'sanctum')
            ->apiJson('PATCH', "/department/subjects/{$subject->id}/lecturer", [
                'lecture_name'          => 'TS. Nguyễn Văn A',
                'lecturer_phone_number' => '0901234567',
                'lecturer_email'        => 'gv@huce.edu.vn',
            ])
            ->assertOk()
            ->assertJsonPath('data.updated_count', 1);

        $this->assertDatabaseHas('remedial_registrations', [
            'subject_id'            => $subject->id,
            'lecture_name'          => 'TS. Nguyễn Văn A',
            'lecturer_phone_number' => '0901234567',
            'lecturer_emal'         => 'gv@huce.edu.vn',
        ]);
    }

    public function test_department_cannot_assign_lecturer_before_registration_end(): void
    {
        $department = Department::where('department_code', '54')->firstOrFail();
        $boMon = User::where('email', 'bokhoa.cntt@remedial.edu.vn')->firstOrFail();

        [, $subject] = $this->seedTermSubjectRegistration(
            $department,
            Carbon::now()->addDays(5)->endOfDay(),
        );

        $this->actingAs($boMon, 'sanctum')
            ->apiJson('PATCH', "/department/subjects/{$subject->id}/lecturer", [
                'lecture_name' => 'TS. Test',
            ])
            ->assertStatus(400)
            ->assertJsonPath('success', false);

        $list = $this->actingAs($boMon, 'sanctum')
            ->apiJson('GET', '/department/subject-assignments')
            ->assertOk()
            ->json('data');

        $this->assertNotEmpty($list);
        $this->assertFalse($list[0]['can_assign_lecturer']);
    }

    public function test_department_subject_assignments_only_shows_current_term(): void
    {
        $department = Department::where('department_code', '54')->firstOrFail();
        $boMon = User::where('email', 'bokhoa.cntt@remedial.edu.vn')->firstOrFail();

        [, $currentSubject] = $this->seedTermSubjectRegistration(
            $department,
            Carbon::now()->subDay()->endOfDay(),
        );

        $oldTerm = RemedialTerm::create([
            'name'                 => 'Đợt cũ',
            'year'                 => 2024,
            'semester'             => 2,
            'start_date'           => Carbon::now()->subMonths(6),
            'end_date'             => Carbon::now()->subMonths(4),
            'registration_start'   => Carbon::now()->subMonths(6)->startOfDay(),
            'registration_end'     => Carbon::now()->subMonths(5)->endOfDay(),
            'remedial_coefficient' => 1,
            'price_per_period'     => 150000,
            'price_coefficient'    => 1,
            'status'               => RemedialTermStatus::COMPLETED,
            'is_deleted'           => false,
        ]);

        $oldSubject = Subject::create([
            'subject_code'  => 'BMOLD01',
            'name'          => 'Môn đợt cũ',
            'credits'       => 3,
            'department_id' => $department->id,
            'is_deleted'    => false,
        ]);

        $student = $this->createStudentUser('SVBMOLD', 'SVBMOLD');

        RemedialRegistration::create([
            'student_id'        => $student->id,
            'subject_id'        => $oldSubject->id,
            'remedial_term_id'  => $oldTerm->id,
            'remedial_periods'  => 45,
            'registration_date' => Carbon::now()->subMonths(5),
            'is_deleted'        => false,
        ]);

        $response = $this->actingAs($boMon, 'sanctum')
            ->apiJson('GET', '/department/subject-assignments')
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $response);
        $this->assertSame('BMTEST01', $response[0]['subject_code']);
        $this->assertSame($currentSubject->id, $response[0]['subject_id']);
    }
}
