<?php

namespace App\Application\Services;

use App\Domain\Entities\TutoringClass;
use App\Domain\Enums\TutoringClassStatus;
use App\Domain\Repositories\TutoringClassRepositoryPort;
use App\Domain\Repositories\CourseRepositoryPort;
use App\Domain\Repositories\TeacherRepositoryPort;
use App\Domain\Repositories\TutoringTermRepositoryPort;
use Carbon\Carbon;

class TutoringClassService
{
    public function __construct(
        private readonly TutoringClassRepositoryPort $classRepository,
        private readonly CourseRepositoryPort        $courseRepository,
        private readonly TeacherRepositoryPort       $teacherRepository,
        private readonly TutoringTermRepositoryPort  $termRepository
    ) {}

    public function createClass(array $data): TutoringClass
    {
        $course = $this->courseRepository->findByCode($data['course_code']);
        if (!$course) {
            throw new \DomainException('Môn học không tồn tại');
        }

        $teacherId = null;
        if (isset($data['teacher_code'])) {
            $teacher = $this->teacherRepository->findByCode($data['teacher_code']);
            $teacherId = $teacher?->id;
        }

        $tutoringClass = new TutoringClass(
            null,
            $course->id,
            $data['tutoring_term_id'],
            $teacherId,
            $data['max_students'],
            0,
            TutoringClassStatus::OPEN,
            Carbon::now()
        );

        return $this->classRepository->save($tutoringClass);
    }

    public function updateClass(int $id, array $data): TutoringClass
    {
        $tutoringClass = $this->classRepository->findById($id);
        if (!$tutoringClass) {
            throw new \DomainException('Không tìm thấy đợt phụ đạo');
        }

        if ($tutoringClass->status === TutoringClassStatus::CANCELLED) {
            throw new \DomainException('Không thể chỉnh sửa đợt phụ đạo đã bị hủy.');
        }

        $newCourseId = $tutoringClass->courseId;
        if (isset($data['course_code'])) {
            $course = $this->courseRepository->findByCode($data['course_code']);
            if ($course) $newCourseId = $course->id;
        }

        $newTeacherId = $tutoringClass->teacherId;
        if (isset($data['teacher_code'])) {
            $teacher = $this->teacherRepository->findByCode($data['teacher_code']);
            $newTeacherId = $teacher?->id;
        }

        $newMaxStudents = $data['max_students'] ?? $tutoringClass->maxStudents;
        $newTermId = $data['tutoring_term_id'] ?? $tutoringClass->tutoringTermId;

        $updatedClass = new TutoringClass(
            id:              $tutoringClass->id,
            courseId:        $newCourseId,
            tutoringTermId:  $newTermId,
            teacherId:       $newTeacherId,
            maxStudents:     $newMaxStudents,
            currentStudents: $tutoringClass->currentStudents,
            status:          $tutoringClass->status,
            createdAt:       $tutoringClass->createdAt
        );

        return $this->classRepository->save($updatedClass);
    }

    public function listClasses(array $filters): array
    {
        return $this->classRepository->findAll($filters);
    }

    public function getClassDetail(int $id): ?TutoringClass
    {
        return $this->classRepository->findById($id);
    }

    public function deleteClass(int $id): void
    {
        $tutoringClass = $this->classRepository->findById($id);
        if (!$tutoringClass) {
            throw new \DomainException('Không tìm thấy đợt phụ đạo');
        }

        if ($tutoringClass->currentStudents > 0) {
            throw new \DomainException("Không thể xóa đợt phụ đạo đang có {$tutoringClass->currentStudents} sinh viên đăng ký.");
        }

        $this->classRepository->delete($id);
    }

    public function assignTeacher(int $id, string $teacherCode): TutoringClass
    {
        $tutoringClass = $this->classRepository->findById($id);
        if (!$tutoringClass) {
            throw new \DomainException('Không tìm thấy đợt phụ đạo');
        }

        $teacher = $this->teacherRepository->findByCode($teacherCode);
        if (!$teacher) {
            throw new \DomainException('Giảng viên không tồn tại');
        }

        $updatedClass = new TutoringClass(
            id:              $tutoringClass->id,
            courseId:        $tutoringClass->courseId,
            tutoringTermId:  $tutoringClass->tutoringTermId,
            teacherId:       $teacher->id,
            maxStudents:     $tutoringClass->maxStudents,
            currentStudents: $tutoringClass->currentStudents,
            status:          $tutoringClass->status,
            createdAt:       $tutoringClass->createdAt
        );

        return $this->classRepository->save($updatedClass);
    }
}
