<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Lớp học bổ sung (Aggregate Root)
 *
 * Tương ứng bảng TUTORING_CLASS trong ERD.
 * Quản lý toàn bộ logic mở/đóng lớp và kiểm soát sĩ số.
 *
 * @property int         $id           ID lớp học
 * @property int         $courseId     FK → COURSE
 * @property int         $tutoringTermId FK → TUTORING_TERM
 * @property int         $teacherId    FK → TEACHER
 * @property int         $maxStudents  Sĩ số tối đa
 * @property string      $status       Trạng thái: open | full | closed | cancelled
 * @property Carbon      $createdAt    Thời điểm tạo lớp
 * @property int         $enrolledCount Số sinh viên đã ghi danh (derived)
 */
class TutoringClass
{
    public const STATUS_OPEN      = 'open';
    public const STATUS_FULL      = 'full';
    public const STATUS_CLOSED    = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct(
        public readonly ?int    $id,
        public readonly int     $courseId,
        public readonly int     $tutoringTermId,
        public readonly ?int    $teacherId = null,
        public readonly ?int    $maxStudents = null,
        public int              $currentStudents = 0,
        public readonly ?int    $totalPeriods = null,
        public string           $status = self::STATUS_OPEN,
        public readonly Carbon  $createdAt = new Carbon(),
        public readonly ?Carbon $updatedAt = null,
    ) {}

    /**
     * Kiểm tra lớp còn chỗ cho sinh viên đăng ký.
     */
    public function hasAvailableSlot(): bool
    {
        return $this->status === self::STATUS_OPEN
            && ($this->maxStudents === null || $this->currentStudents < $this->maxStudents);
    }

    /**
     * Số chỗ còn trống.
     */
    public function availableSlots(): ?int
    {
        if ($this->maxStudents === null) return null;
        return max(0, $this->maxStudents - $this->currentStudents);
    }

    /**
     * Ghi nhận thêm một sinh viên ghi danh vào lớp.
     * Tự động chuyển trạng thái sang FULL nếu đầy.
     *
     * @throws \DomainException Nếu lớp không còn chỗ trống
     */
    public function incrementEnrollment(): void
    {
        if (! $this->hasAvailableSlot()) {
            throw new \DomainException('Lớp học bổ sung đã đầy hoặc không còn mở.');
        }

        $this->currentStudents++;

        if ($this->maxStudents !== null && $this->currentStudents >= $this->maxStudents) {
            $this->status = self::STATUS_FULL;
        }
    }

    /**
     * Giảm sĩ số khi một sinh viên hủy ghi danh.
     * Tự động mở lại lớp nếu trước đó đầy.
     */
    public function decrementEnrollment(): void
    {
        if ($this->currentStudents > 0) {
            $this->currentStudents--;
        }

        if ($this->status === self::STATUS_FULL && ($this->maxStudents === null || $this->currentStudents < $this->maxStudents)) {
            $this->status = self::STATUS_OPEN;
        }
    }

    /**
     * Đóng lớp (không nhận thêm sinh viên dù còn chỗ).
     *
     * @throws \DomainException Nếu lớp đã bị hủy
     */
    public function close(): void
    {
        if ($this->status === self::STATUS_CANCELLED) {
            throw new \DomainException('Không thể đóng lớp đã bị hủy.');
        }

        $this->status = self::STATUS_CLOSED;
    }

    /**
     * Hủy lớp học.
     *
     * @throws \DomainException Nếu lớp đã có sinh viên ghi danh
     */
    public function cancel(): void
    {
        if ($this->currentStudents > 0) {
            throw new \DomainException('Không thể hủy lớp đã có sinh viên ghi danh.');
        }

        $this->status = self::STATUS_CANCELLED;
    }

    /**
     * Lớp có đang mở không (open hoặc full nhưng chưa closed/cancelled).
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_FULL], true);
    }
}
