<?php

namespace Tests\Support;

use App\Domain\Entities\StudentInfo;
use App\Domain\Entities\SubjectResult;
use App\Domain\Entities\TermRegisteredCourse;
use App\Domain\Ports\External\StudentInfoPort;

/**
 * Fake University System cho PHPUnit (không gọi HTTP).
 */
final class FakeStudentInfoPort implements StudentInfoPort
{
    /** @var array<string, string> student_code => password */
    private array $credentials = [];

    /** @var array<string, StudentInfo> */
    private array $students = [];

    /** @var array<string, SubjectResult[]> */
    private array $courses = [];

    /** @var array<string, TermRegisteredCourse[]> key: CODE:year:semester */
    private array $registeredCourses = [];

    public function registerStudent(
        string $studentCode,
        string $password,
        StudentInfo $info,
        array $courses = [],
        array $termRegisteredCourses = [],
        int $termYear = 2023,
        int $termSemester = 1,
    ): void {
        $code = strtoupper($studentCode);
        $this->credentials[$code] = $password;
        $this->students[$code]      = $info;
        $this->courses[$code]       = $courses;
        $this->registeredCourses["{$code}:{$termYear}:{$termSemester}"] = $termRegisteredCourses;
    }

    public function getStudent(string $studentCode): StudentInfo
    {
        $code = strtoupper($studentCode);

        if (! isset($this->students[$code])) {
            throw new \App\Domain\Exceptions\StudentNotFoundException(
                "Mã sinh viên '{$code}' không tồn tại (fake)."
            );
        }

        return $this->students[$code];
    }

    public function getCourses(string $studentCode): array
    {
        $code = strtoupper($studentCode);

        return $this->courses[$code] ?? [];
    }

    public function getRegisteredCoursesForSemester(string $studentCode, int $year, int $semester): array
    {
        $code = strtoupper($studentCode);
        $key  = "{$code}:{$year}:{$semester}";

        return $this->registeredCourses[$key] ?? [];
    }

    public function verifyCredentials(string $studentCode, string $password): bool
    {
        $code = strtoupper($studentCode);

        return isset($this->credentials[$code]) && $this->credentials[$code] === $password;
    }
}
