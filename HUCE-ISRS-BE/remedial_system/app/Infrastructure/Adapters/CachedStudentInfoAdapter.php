<?php

namespace App\Infrastructure\Adapters;

use App\Domain\Entities\StudentInfo;
use App\Domain\Ports\StudentInfoPort;
use Illuminate\Support\Facades\Cache;

class CachedStudentInfoAdapter implements StudentInfoPort
{
    public function __construct(
        private readonly StudentInfoPort $innerAdapter,
        private readonly int $ttlSeconds = 3600, // Mặc định cache 1 giờ
    ) {}

    public function getStudent(string $studentCode): StudentInfo
    {
        return Cache::remember(
            "student_info:{$studentCode}",
            $this->ttlSeconds,
            fn() => $this->innerAdapter->getStudent($studentCode)
        );
    }

    public function getCourses(string $studentCode): array
    {
        return Cache::remember(
            "student_courses:{$studentCode}",
            $this->ttlSeconds,
            fn() => $this->innerAdapter->getCourses($studentCode)
        );
    }

    public function verifyCredentials(string $studentCode, string $password): bool
    {
        // Không cache thông tin xác thực
        return $this->innerAdapter->verifyCredentials($studentCode, $password);
    }
}
