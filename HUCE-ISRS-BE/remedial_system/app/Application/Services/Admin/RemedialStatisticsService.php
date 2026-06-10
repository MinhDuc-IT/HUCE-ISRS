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

    public function getTeachingPaymentStatisticsQuery(int $termId, ?string $keyword = null)
    {
        $query = \Illuminate\Support\Facades\DB::table('remedial_registrations')
            ->join('subjects', 'remedial_registrations.subject_id', '=', 'subjects.id')
            ->join('remedial_terms', 'remedial_registrations.remedial_term_id', '=', 'remedial_terms.id')
            ->where('remedial_registrations.remedial_term_id', $termId)
            ->where('remedial_registrations.is_deleted', false)
            ->where('subjects.is_deleted', false);

        if (!empty($keyword)) {
            $query->where('remedial_registrations.lecture_name', 'like', '%' . $keyword . '%');
        }

        $query->selectRaw('
            MAX(remedial_registrations.id) as id,
            remedial_registrations.lecture_name as lecturer_name,
            MAX(remedial_registrations.lecturer_phone_number) as lecturer_phone,
            subjects.subject_code,
            subjects.name as subject_name,
            MAX(remedial_registrations.remedial_periods) as remedial_periods,
            MAX(remedial_terms.price_per_period) as price_per_period,
            MAX(remedial_terms.price_coefficient) as price_coefficient
        ')
        ->groupBy(
            'subjects.id',
            'subjects.subject_code',
            'subjects.name',
            'remedial_registrations.lecture_name'
        )
        ->orderBy('subjects.subject_code');

        return $query;
    }

    public function getTeachingPaymentStatistics(int $termId, ?string $keyword = null, int $perPage = 15): array
    {
        $term = $this->termRepository->findById($termId);
        if ($term === null) {
            throw new \DomainException('Không tìm thấy đợt phụ đạo.');
        }

        $query = $this->getTeachingPaymentStatisticsQuery($termId, $keyword);
        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(function ($row) {
            $pricePerPeriod = (float) $row->price_per_period;
            $priceCoefficient = (float) $row->price_coefficient;
            $baseAmount = $pricePerPeriod * $priceCoefficient;
            $totalAmount = (int) $row->remedial_periods * $baseAmount;

            return [
                'id'               => $row->id,
                'lecturer_name'    => $row->lecturer_name,
                'lecturer_phone'   => $row->lecturer_phone,
                'subject_code'     => $row->subject_code,
                'subject_name'     => $row->subject_name,
                'remedial_periods' => (int) $row->remedial_periods,
                'price_per_period' => $pricePerPeriod,
                'base_amount'      => $baseAmount,
                'total_amount'     => $totalAmount,
            ];
        });

        return [
            'data'         => $paginator->items(),
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
        ];
    }
}
