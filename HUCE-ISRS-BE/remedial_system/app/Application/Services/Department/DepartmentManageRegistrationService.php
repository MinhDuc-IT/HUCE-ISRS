<?php

namespace App\Application\Services\Department;

use App\Application\Services\RemedialTermResolver;
use App\Domain\Entities\RemedialRegistration;
use App\Domain\Ports\Persistence\RemedialRegistrationRepositoryPort;
use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;
use App\Events\LecturerAssignedToSubject;
use App\Models\RemedialRegistration as RemedialRegistrationModel;
use App\Models\Teacher;
use App\Models\User;

class DepartmentManageRegistrationService
{
    public function __construct(
        private readonly RemedialRegistrationRepositoryPort $registrationRepository,
        private readonly SubjectRepositoryPort $subjectRepository,
        private readonly RemedialTermRepositoryPort $termRepository,
        private readonly DepartmentProfileService $profileService,
        private readonly RemedialTermResolver $termResolver,
    ) {}

    public function updateLecturer(User $user, int $registrationId, array $data): RemedialRegistration
    {
        $departmentId = $this->profileService->resolveDepartmentId($user);
        $registration = $this->registrationRepository->findById($registrationId);

        if ($registration === null) {
            throw new \DomainException("Không tìm thấy đơn đăng ký #{$registrationId}.");
        }

        $subject = $this->subjectRepository->findById($registration->subjectId);

        if ($subject === null || $subject->departmentId !== $departmentId) {
            throw new \DomainException('Đơn đăng ký không thuộc bộ môn của bạn.');
        }

        $updated = new RemedialRegistration(
            id:                  $registration->id,
            studentId:           $registration->studentId,
            subjectId:           $registration->subjectId,
            remedialTermId:      $registration->remedialTermId,
            remedialPeriods:     $registration->remedialPeriods,
            registrationDate:    $registration->registrationDate,
            lectureName:         array_key_exists('lecture_name', $data) ? $data['lecture_name'] : $registration->lectureName,
            lecturerPhoneNumber: array_key_exists('lecturer_phone_number', $data)
                ? $data['lecturer_phone_number']
                : $registration->lecturerPhoneNumber,
            lecturerEmail:       array_key_exists('lecturer_email', $data)
                ? $data['lecturer_email']
                : $registration->lecturerEmail,
        );

        return $this->registrationRepository->save($updated);
    }

    /**
     * Gán giảng viên cho mọi đăng ký phụ đạo của một môn thuộc bộ môn.
     *
     * @return int Số bản ghi remedial_registrations đã cập nhật
     */
    public function updateLecturerForSubject(User $user, int $subjectId, array $data, ?int $remedialTermId = null): int
    {
        $departmentId = $this->profileService->resolveDepartmentId($user);
        $termId = $this->termResolver->requireCurrentTermId($remedialTermId);
        $subject      = $this->subjectRepository->findById($subjectId);

        if ($subject === null || $subject->departmentId !== $departmentId) {
            throw new \DomainException('Môn học không thuộc bộ môn của bạn.');
        }

        $this->assertRegistrationPeriodClosedForSubject($subjectId, $departmentId, $termId);

        // Nếu có teacher_id, lấy thông tin giáo viên từ hệ thống
        if (!empty($data['teacher_id'])) {
            $teacher = Teacher::where('id', $data['teacher_id'])
                ->where('department_id', $departmentId)
                ->first();

            if ($teacher === null) {
                throw new \DomainException('Giáo viên không thuộc bộ môn của bạn.');
            }

            $data['lecture_name'] = trim($teacher->last_name . ' ' . $teacher->first_name);
            $data['lecturer_email'] = $teacher->email;
            $data['lecturer_phone_number'] = $teacher->phone ?? '';
        }

        $updated = $this->registrationRepository->bulkUpdateLecturerForSubject(
            $subjectId,
            $departmentId,
            $termId,
            $data
        );

        if ($updated === 0) {
            throw new \DomainException('Không có đăng ký phụ đạo nào cho môn học này.');
        }

        $lecturerEmail = trim((string) ($data['lecturer_email'] ?? ''));
        if ($lecturerEmail !== '' && filter_var($lecturerEmail, FILTER_VALIDATE_EMAIL)) {
            event(new LecturerAssignedToSubject(
                subjectId: $subjectId,
                departmentId: $departmentId,
                lecturerEmail: $lecturerEmail,
                lecturerName: $data['lecture_name'] ?? null,
                lecturerPhoneNumber: $data['lecturer_phone_number'] ?? null,
                updatedCount: $updated,
                assignedBy: $user->name,
            ));
        }

        return $updated;
    }

    private function assertRegistrationPeriodClosedForSubject(
        int $subjectId,
        int $departmentId,
        int $remedialTermId,
    ): void {
        $hasRegistration = RemedialRegistrationModel::query()
            ->where('subject_id', $subjectId)
            ->where('remedial_term_id', $remedialTermId)
            ->where('is_deleted', false)
            ->whereHas('subject', fn ($q) => $q
                ->where('department_id', $departmentId)
                ->where('is_deleted', false))
            ->exists();

        if (! $hasRegistration) {
            throw new \DomainException('Không có đăng ký phụ đạo nào cho môn học này trong đợt hiện tại.');
        }

        $term = $this->termRepository->findById($remedialTermId);

        if ($term !== null && $term->isRegistrationOpen()) {
            throw new \DomainException(
                'Chỉ được gán giảng viên sau khi hết thời gian đăng ký phụ đạo (sau ngày đóng đăng ký của đợt).'
            );
        }
    }
}
