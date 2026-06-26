<?php

namespace Tests;

use App\Domain\Entities\StudentInfo;
use App\Domain\Entities\SubjectResult;
use App\Domain\Entities\TermRegisteredCourse;
use App\Domain\Ports\External\StudentInfoPort;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\FakeStudentInfoPort;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected FakeStudentInfoPort $fakeStudentInfo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeStudentInfo = new FakeStudentInfoPort();
        $this->app->instance(StudentInfoPort::class, $this->fakeStudentInfo);

        $this->seed(DatabaseSeeder::class);
    }

    protected function actingAsAdmin(): static
    {
        $admin = User::where('email', 'admin@remedial.edu.vn')->firstOrFail();

        return $this->actingAs($admin, 'sanctum');
    }

    protected function actingAsBoMon(): static
    {
        $user = User::where('email', 'bokhoa.cntt@remedial.edu.vn')->firstOrFail();

        return $this->actingAs($user, 'sanctum');
    }

    protected function createStudentUser(string $code = 'SVTEST', string $password = 'SVTEST'): User
    {
        $this->fakeStudentInfo->registerStudent(
            $code,
            $password,
            new StudentInfo(
                id: $code,
                fullName: 'Sinh viên Test',
                gender: 'Nam',
                dateOfBirth: '2000-01-01',
                placeOfBirth: 'Huế',
                personalEmail: strtolower($code) . '@test.edu.vn',
                universityEmail: null,
                gpaScale10: 5.0,
                gpaScale4: 2.0,
                gradeClassification: 'Trung bình',
                totalCredits: 100,
                failedCredits: 6,
            ),
            [
                new SubjectResult(
                    courseCode: 'CS101',
                    subjectCode: 'CS101',
                    subjectName: 'Lập trình căn bản',
                    credits: 3,
                    classSectionCode: 'CS101-01',
                    semesterOrder: 1,
                    academicYear: 2024,
                    finalScore: 3.0,
                    gpaScore: 1.0,
                    letterGrade: 'F',
                ),
            ],
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
                    examDate: '2025-12-15',
                ),
            ],
            2025,
            1,
        );

        return User::create([
            'name'          => 'Sinh viên Test',
            'email'         => strtolower($code) . '@student.remedial.edu.vn',
            'password'      => Hash::make($password),
            'role'          => User::ROLE_SINH_VIEN,
            'student_code'  => strtoupper($code),
            'department_id' => null,
            'is_deleted'    => false,
        ]);
    }

    protected function apiJson(string $method, string $uri, array $data = [], array $headers = [])
    {
        return $this->json($method, '/api' . $uri, $data, array_merge([
            'Accept' => 'application/json',
        ], $headers));
    }
}
