<?php

namespace App\Application\Services;

use App\Domain\Entities\RemedialRegistration;
use App\Domain\Ports\External\StudentInfoPort;
use App\Domain\Ports\Persistence\RemedialRegistrationRepositoryPort;
use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;
use App\Domain\Ports\Persistence\SystemConfigurationRepositoryPort;
use App\Domain\Enums\SystemConfigKey;
use App\Models\User;
use Carbon\Carbon;

class RemedialRegistrationService
{
    private const EXAM_DATE_WINDOW_DAYS = 60;

    public function __construct(
        private readonly StudentInfoPort $studentInfoPort,
        private readonly RemedialRegistrationRepositoryPort $registrationRepository,
        private readonly SystemConfigurationRepositoryPort $configRepository,
        private readonly RemedialTermRepositoryPort $termRepository,
        private readonly SubjectRepositoryPort $subjectRepository,
    ) {}

    public function registerForUser(User $user, string $courseCode, ?int $remedialPeriods = null): RemedialRegistration
    {
        $this->assertSinhVien($user);
        $studentCode = $this->requireStudentCode($user);

        $currentTerm = $this->termRepository->findCurrent();
        if ($currentTerm === null) {
            throw new \DomainException('Hệ thống hiện không có đợt phụ đạo nào đang mở.');
        }

        if (! $currentTerm->isRegistrationOpen()) {
            throw new \DomainException('Hiện không trong thời gian đăng ký phụ đạo của đợt này.');
        }

        $termCourses = $this->getRegisteredCoursesForCurrentTerm($studentCode, $currentTerm);

        $targetCourse = collect($termCourses)->first(
            fn ($course) => strtoupper($course->code()) === strtoupper(trim($courseCode))
        );

        if ($targetCourse === null) {
            throw new \DomainException(
                "Học phần {$courseCode} không thuộc danh sách môn đã đăng ký học chính quy kỳ {$currentTerm->semester}/{$currentTerm->year}."
            );
        }

        $localSubject = $this->subjectRepository->findByCode($courseCode);
        if ($localSubject === null) {
            throw new \DomainException("Dữ liệu môn học {$courseCode} chưa được đồng bộ.");
        }

        if ($localSubject->credits <= 1) {
            throw new \DomainException("Môn học {$courseCode} có số tín chỉ <= 1, không được phép đăng ký phụ đạo.");
        }

        if ($this->registrationRepository->existsRegistration($user->id, $localSubject->id, $currentTerm->id)) {
            throw new \DomainException("Bạn đã đăng ký môn {$courseCode} trong đợt này rồi.");
        }

        $periods = $remedialPeriods ?? (int) $this->configRepository->get(
            SystemConfigKey::DEFAULT_PERIODS->value,
            '45'
        );

        $registration = new RemedialRegistration(
            id:               null,
            studentId:        $user->id,
            subjectId:        $localSubject->id,
            remedialTermId:   $currentTerm->id,
            remedialPeriods:  $periods,
            registrationDate: Carbon::now(),
        );

        return $this->registrationRepository->save($registration);
    }

    public function bulkRegisterForUser(User $user, array $courseCodes): array
    {
        foreach ($courseCodes as $code) {
            $this->registerForUser($user, $code);
        }

        return $this->getRegistrationsForUser($user);
    }

    /** @return RemedialRegistration[] */
    public function getRegistrationsForUser(User $user, ?int $remedialTermId = null): array
    {
        $this->assertSinhVien($user);

        return $this->registrationRepository->findByUser($user->id, $remedialTermId);
    }

    /** @return \App\Domain\Entities\TermRegisteredCourse[] */
    public function getTermRegisteredSubjectsForUser(User $user): array
    {
        $this->assertSinhVien($user);
        $studentCode = $this->requireStudentCode($user);

        $currentTerm = $this->termRepository->findCurrent();
        if ($currentTerm === null) {
            throw new \DomainException('Hệ thống hiện không có đợt phụ đạo nào đang mở.');
        }

        $this->studentInfoPort->getStudent($studentCode);

        return $this->getRegisteredCoursesForCurrentTerm($studentCode, $currentTerm);
    }

    /**
     * @return \App\Domain\Entities\TermRegisteredCourse[]
     */
    private function getRegisteredCoursesForCurrentTerm(string $studentCode, \App\Domain\Entities\RemedialTerm $currentTerm): array
    {
        $courses = $this->studentInfoPort->getRegisteredCoursesForSemester(
            $studentCode,
            $currentTerm->year,
            $currentTerm->semester
        );

        return $this->filterCoursesByExamWindow($courses, $currentTerm);
    }

    /**
     * Mỗi đợt phụ đạo gắn với một block thi: chỉ lấy môn có ngày thi trong
     * [registration_start, registration_start + 60 ngày] để loại môn block kia.
     *
     * @param  \App\Domain\Entities\TermRegisteredCourse[]  $courses
     * @return \App\Domain\Entities\TermRegisteredCourse[]
     */
    private function filterCoursesByExamWindow(array $courses, \App\Domain\Entities\RemedialTerm $term): array
    {
        if ($term->registrationStart === null) {
            return $courses;
        }

        $windowStart = $term->registrationStart->copy()->startOfDay();
        $windowEnd = $windowStart->copy()->addDays(self::EXAM_DATE_WINDOW_DAYS)->endOfDay();

        return array_values(array_filter(
            $courses,
            fn ($course) => $this->isExamDateWithinWindow($course->examDate, $windowStart, $windowEnd)
        ));
    }

    private function isExamDateWithinWindow(?string $examDate, Carbon $windowStart, Carbon $windowEnd): bool
    {
        if ($examDate === null || trim($examDate) === '') {
            return false;
        }

        $parsedExamDate = Carbon::parse($examDate);

        return $parsedExamDate->greaterThanOrEqualTo($windowStart)
            && $parsedExamDate->lessThanOrEqualTo($windowEnd);
    }

    /** @return \App\Domain\Entities\SubjectResult[] */
    public function getEligibleSubjectsForUser(User $user): array
    {
        $this->assertSinhVien($user);
        $studentCode = $this->requireStudentCode($user);

        $this->studentInfoPort->getStudent($studentCode);

        $courses = $this->studentInfoPort->getCourses($studentCode);

        return array_values(array_filter(
            $courses,
            fn ($course) => $course->isEligibleForRemedial()
        ));
    }

    public function cancelRegistrationForUser(User $user, int $registrationId): void
    {
        $this->assertSinhVien($user);

        $registration = $this->registrationRepository->findById($registrationId);

        if ($registration === null || $registration->studentId !== $user->id) {
            throw new \DomainException("Không tìm thấy đơn đăng ký #{$registrationId}.");
        }

        $this->registrationRepository->delete($registrationId);
    }

    private function assertSinhVien(User $user): void
    {
        if (! $user->isSinhVien()) {
            throw new \DomainException('Chỉ sinh viên mới được thực hiện thao tác này.');
        }
    }

    private function requireStudentCode(User $user): string
    {
        $code = $user->student_code;

        if ($code === null || trim($code) === '') {
            throw new \DomainException('Tài khoản chưa có mã sinh viên.');
        }

        return strtoupper(trim($code));
    }
}
