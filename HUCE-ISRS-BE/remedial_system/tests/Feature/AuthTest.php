<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_admin_can_login_with_email_and_password(): void
    {
        $response = $this->apiJson('POST', '/auth/login', [
            'email'    => 'admin@remedial.edu.vn',
            'password' => 'Admin@2024!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'admin')
            ->assertJsonPath('data.user.home_url', '/admin')
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->apiJson('POST', '/auth/login', [
            'email'    => 'admin@remedial.edu.vn',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_fetch_me(): void
    {
        $response = $this->actingAsAdmin()
            ->apiJson('GET', '/auth/me');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'admin@remedial.edu.vn')
            ->assertJsonPath('data.home_url', '/admin');
    }

    public function test_student_can_login_with_student_code_via_fake_university(): void
    {
        $this->fakeStudentInfo->registerStudent(
            'SVLOGIN',
            'secret123',
            $this->createStudentInfo('SVLOGIN'),
            []
        );

        $response = $this->apiJson('POST', '/auth/login', [
            'student_code' => 'SVLOGIN',
            'password'     => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.role', 'sinh_vien')
            ->assertJsonPath('data.user.home_url', '/student');

        $this->assertDatabaseHas('users', [
            'student_code' => 'SVLOGIN',
            'role'         => User::ROLE_SINH_VIEN,
            'is_deleted'   => false,
        ]);
    }

    private function createStudentInfo(string $code): \App\Domain\Entities\StudentInfo
    {
        return new \App\Domain\Entities\StudentInfo(
            id: $code,
            fullName: 'SV Login Test',
            gender: 'Nam',
            dateOfBirth: '2000-01-01',
            placeOfBirth: 'Huế',
            personalEmail: 'svlogin@test.edu.vn',
            universityEmail: null,
            gpaScale10: 5.0,
            gpaScale4: 2.0,
            gradeClassification: null,
            totalCredits: 80,
            failedCredits: 3,
        );
    }
}
