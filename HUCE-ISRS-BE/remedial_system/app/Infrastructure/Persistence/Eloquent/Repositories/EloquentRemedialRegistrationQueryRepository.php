<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Ports\Persistence\RemedialRegistrationQueryPort;
use App\Models\RemedialRegistration as RemedialRegistrationModel;
use Illuminate\Support\Facades\DB;

class EloquentRemedialRegistrationQueryRepository implements RemedialRegistrationQueryPort
{
    public function listForAdmin(
        ?int $remedialTermId = null,
        ?int $departmentId = null,
        ?int $subjectId = null,
        ?string $studentCode = null,
    ): array {
        $query = RemedialRegistrationModel::query()
            ->with(['user', 'subject', 'remedialTerm'])
            ->orderByDesc('registration_date');

        if ($remedialTermId !== null) {
            $query->where('remedial_term_id', $remedialTermId);
        }

        if ($subjectId !== null) {
            $query->where('subject_id', $subjectId);
        }

        if ($departmentId !== null) {
            $query->whereHas('subject', fn ($q) => $q
                ->where('department_id', $departmentId)
                ->where('is_deleted', false));
        }

        if ($studentCode !== null && $studentCode !== '') {
            $code = strtoupper(trim($studentCode));
            $query->whereHas('user', fn ($q) => $q->where('student_code', $code));
        }

        return $query->get()->map(fn ($reg) => [
            'id'                 => $reg->id,
            'student_id'         => $reg->student_id,
            'student_code'       => $reg->user?->student_code,
            'student_name'       => $reg->user?->name,
            'subject_id'         => $reg->subject_id,
            'subject_code'       => $reg->subject?->subject_code,
            'subject_name'       => $reg->subject?->name,
            'remedial_term_id'   => $reg->remedial_term_id,
            'remedial_term_name' => $reg->remedialTerm?->name,
            'remedial_periods'   => $reg->remedial_periods,
            'registration_date'  => $reg->registration_date?->toIso8601String(),
            'lecture_name'       => $reg->lecture_name,
            'lecturer_phone'     => $reg->lecturer_phone_number,
            'lecturer_email'     => $reg->lecturer_emal,
        ])->all();
    }

    public function listGroupedForAdmin(
        ?int $remedialTermId = null,
        ?int $departmentId = null,
        ?int $subjectId = null,
        ?string $studentCode = null,
    ): array {
        $query = DB::table('remedial_registrations as rr')
            ->join('subjects as s', 'rr.subject_id', '=', 's.id')
            ->join('remedial_terms as rt', 'rr.remedial_term_id', '=', 'rt.id')
            ->where('rr.is_deleted', false)
            ->where('s.is_deleted', false)
            ->where('rt.is_deleted', false);

        if ($remedialTermId !== null) {
            $query->where('rr.remedial_term_id', $remedialTermId);
        }

        if ($subjectId !== null) {
            $query->where('rr.subject_id', $subjectId);
        }

        if ($departmentId !== null) {
            $query->where('s.department_id', $departmentId);
        }

        if ($studentCode !== null && $studentCode !== '') {
            $code = strtoupper(trim($studentCode));
            $query->join('users as u', 'rr.student_id', '=', 'u.id')
                ->where('u.student_code', $code);
        }

        return $query
            ->selectRaw('
                rr.remedial_term_id,
                rt.name as remedial_term_name,
                s.id as subject_id,
                s.subject_code,
                s.name as subject_name,
                COUNT(rr.id) as student_count,
                MAX(rr.lecture_name) as lecture_name
            ')
            ->groupBy(
                'rr.remedial_term_id',
                'rt.name',
                's.id',
                's.subject_code',
                's.name',
            )
            ->orderByDesc('rr.remedial_term_id')
            ->orderBy('s.subject_code')
            ->get()
            ->map(fn ($row) => [
                'remedial_term_id'   => (int) $row->remedial_term_id,
                'remedial_term_name' => $row->remedial_term_name,
                'subject_id'         => (int) $row->subject_id,
                'subject_code'       => $row->subject_code,
                'subject_name'       => $row->subject_name,
                'student_count'      => (int) $row->student_count,
                'lecture_name'       => $row->lecture_name,
            ])
            ->all();
    }

    public function listStudentsForAdminGroup(int $remedialTermId, int $subjectId): array
    {
        return RemedialRegistrationModel::query()
            ->with(['user', 'subject', 'remedialTerm'])
            ->where('remedial_term_id', $remedialTermId)
            ->where('subject_id', $subjectId)
            ->orderBy('registration_date')
            ->get()
            ->map(fn ($reg) => [
                'id'                => $reg->id,
                'student_code'      => $reg->user?->student_code,
                'student_name'      => $reg->user?->name,
                'registration_date' => $reg->registration_date?->toIso8601String(),
                'remedial_term_id'  => $reg->remedial_term_id,
                'remedial_term_name'=> $reg->remedialTerm?->name,
                'subject_id'        => $reg->subject_id,
                'subject_code'      => $reg->subject?->subject_code,
                'subject_name'      => $reg->subject?->name,
            ])
            ->all();
    }

    public function listSubjectAssignmentSummaries(int $departmentId, ?int $remedialTermId = null): array
    {
        $query = DB::table('remedial_registrations')
            ->join('subjects', 'remedial_registrations.subject_id', '=', 'subjects.id')
            ->join('departments', 'subjects.department_id', '=', 'departments.id')
            ->where('remedial_registrations.is_deleted', false)
            ->where('subjects.department_id', $departmentId)
            ->where('subjects.is_deleted', false);

        if ($remedialTermId !== null) {
            $query->where('remedial_registrations.remedial_term_id', $remedialTermId);
        }

        return $query
            ->selectRaw('
                subjects.id as subject_id,
                subjects.subject_code,
                subjects.name as subject_name,
                departments.name as department_name,
                subjects.credits,
                COUNT(remedial_registrations.id) as registration_count,
                MAX(remedial_registrations.lecture_name) as lecture_name,
                MAX(remedial_registrations.lecturer_phone_number) as lecturer_phone,
                MAX(remedial_registrations.lecturer_emal) as lecturer_email
            ')
            ->groupBy(
                'subjects.id',
                'subjects.subject_code',
                'subjects.name',
                'departments.name',
                'subjects.credits'
            )
            ->orderBy('subjects.subject_code')
            ->get()
            ->map(fn ($row) => [
                'subject_id'          => (int) $row->subject_id,
                'subject_code'        => $row->subject_code,
                'subject_name'        => $row->subject_name,
                'department_name'     => $row->department_name,
                'credits'             => (int) $row->credits,
                'registration_count'  => (int) $row->registration_count,
                'lecture_name'        => $row->lecture_name,
                'lecturer_phone'      => $row->lecturer_phone,
                'lecturer_email'      => $row->lecturer_email,
            ])
            ->all();
    }
}
