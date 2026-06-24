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

    /** @return array<int, array<string, mixed>> */
    public function listAllTermSummaries(): array
    {
        $summaries = [];

        foreach ($this->termRepository->findAll() as $term) {
            $stats = $this->computeTermStatistics($term);

            $summaries[] = [
                'remedial_term_id'                => $term->id,
                'remedial_term_name'              => $term->name,
                'distinct_student_count'          => $stats['distinct_student_count'],
                'courses_with_registration_count' => $stats['courses_with_registration_count'],
                'total_revenue'                   => $stats['total_revenue'],
            ];
        }

        return $summaries;
    }

    public function getTermStatistics(int $termId): array
    {
        $term = $this->termRepository->findById($termId);

        if ($term === null) {
            throw new \DomainException('Không tìm thấy đợt phụ đạo.');
        }

        $stats = $this->computeTermStatistics($term);

        return [
            'remedial_term_id'                 => $termId,
            'distinct_student_count'           => $stats['distinct_student_count'],
            'catalog_course_count'             => count($this->subjectRepository->findAll()),
            'courses_with_registration_count'  => $stats['courses_with_registration_count'],
            'assigned_class_count'             => $stats['assigned_class_count'],
            'total_registrations'              => $stats['total_registrations'],
            'total_revenue'                    => $stats['total_revenue'],
        ];
    }

    /** @return array{distinct_student_count: int, courses_with_registration_count: int, assigned_class_count: int, total_registrations: int, total_revenue: int} */
    private function computeTermStatistics(\App\Domain\Entities\RemedialTerm $term): array
    {
        $registrations = RemedialRegistrationModel::where('remedial_term_id', $term->id)
            ->where('is_deleted', false)
            ->get();

        $totalRevenue = $registrations->sum(
            fn ($reg) => $reg->remedial_periods
                * $term->pricePerPeriod
                * $term->priceCoefficient
        );

        return [
            'distinct_student_count'          => $registrations->pluck('student_id')->unique()->count(),
            'courses_with_registration_count' => $registrations->pluck('subject_id')->unique()->count(),
            'assigned_class_count'            => $registrations
                ->filter(fn ($reg) => trim((string) ($reg->lecture_name ?? '')) !== '')
                ->count(),
            'total_registrations'             => $registrations->count(),
            'total_revenue'                   => $totalRevenue,
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
