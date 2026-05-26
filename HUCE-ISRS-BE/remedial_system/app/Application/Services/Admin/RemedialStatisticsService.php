<?php

namespace App\Application\Services\Admin;

use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;
use App\Models\RemedialRegistration as RemedialRegistrationModel;

class RemedialStatisticsService
{
    public function __construct(
        private readonly RemedialTermRepositoryPort $termRepository,
        private readonly SubjectRepositoryPort $subjectRepository,
    ) {}

    public function listTermOptions(): array
    {
        return array_map(
            fn ($term) => ['id' => $term->id, 'name' => $term->name],
            $this->termRepository->findAll()
        );
    }

    public function getTermStatistics(int $termId): array
    {
        $term = $this->termRepository->findById($termId);

        if ($term === null) {
            throw new \DomainException('Không tìm thấy đợt phụ đạo.');
        }

        $registrations = RemedialRegistrationModel::where('remedial_term_id', $termId)->get();

        $distinctStudentCount = $registrations->pluck('student_id')->unique()->count();
        $subjectsWithRegistration = $registrations->pluck('subject_id')->unique()->count();
        $catalogCourseCount = count($this->subjectRepository->findAll());

        $totalRevenue = $registrations->sum(
            fn ($reg) => $reg->remedial_periods
                * $term->pricePerPeriod
                * $term->priceCoefficient
        );

        $assignedClassCount = $registrations
            ->filter(fn ($reg) => trim((string) ($reg->lecture_name ?? '')) !== '')
            ->count();

        return [
            'remedial_term_id'                 => $termId,
            'distinct_student_count'           => $distinctStudentCount,
            'catalog_course_count'             => $catalogCourseCount,
            'courses_with_registration_count'  => $subjectsWithRegistration,
            'assigned_class_count'             => $assignedClassCount,
            'total_registrations'              => $registrations->count(),
            'total_revenue'                    => $totalRevenue,
        ];
    }
}
