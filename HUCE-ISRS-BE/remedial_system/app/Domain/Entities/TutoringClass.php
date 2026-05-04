<?php

namespace App\Domain\Entities;

use App\Domain\Enums\TutoringClassStatus;
use Carbon\Carbon;

/**
 * Domain Entity – Lớp học bổ sung (Aggregate Root)
 */
class TutoringClass
{
    public readonly ?int $id;
    public readonly int $courseId;
    public readonly int $tutoringTermId;
    public readonly ?int $teacherId;
    public readonly int $maxStudents;
    public int $currentStudents;
    public TutoringClassStatus $status;
    public readonly Carbon $createdAt;
    public readonly ?Carbon $updatedAt;

    public function __construct(
        ?int $id,
        int $courseId,
        int $tutoringTermId,
        ?int $teacherId,
        int $maxStudents,
        int $currentStudents,
        TutoringClassStatus $status,
        Carbon $createdAt,
        ?Carbon $updatedAt = null
    ) {
        $this->id = $id;
        $this->courseId = $courseId;
        $this->tutoringTermId = $tutoringTermId;
        $this->teacherId = $teacherId;
        $this->maxStudents = $maxStudents;
        $this->currentStudents = $currentStudents;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function hasAvailableSlot(): bool
    {
        return $this->status === TutoringClassStatus::OPEN
            && ($this->currentStudents < $this->maxStudents);
    }

    public function availableSlots(): int
    {
        return max(0, $this->maxStudents - $this->currentStudents);
    }

    public function incrementEnrollment(): void
    {
        if (! $this->hasAvailableSlot()) {
            throw new \DomainException('Lớp học bổ sung đã đầy hoặc không còn mở.');
        }
        $this->currentStudents++;
        if ($this->currentStudents >= $this->maxStudents) {
            $this->status = TutoringClassStatus::FULL;
        }
    }

    public function decrementEnrollment(): void
    {
        if ($this->currentStudents > 0) {
            $this->currentStudents--;
        }
        if ($this->status === TutoringClassStatus::FULL && $this->currentStudents < $this->maxStudents) {
            $this->status = TutoringClassStatus::OPEN;
        }
    }

    public function close(): void
    {
        if ($this->status === TutoringClassStatus::CANCELLED) {
            throw new \DomainException('Không thể đóng lớp đã bị hủy.');
        }
        $this->status = TutoringClassStatus::CLOSED;
    }

    public function cancel(): void
    {
        if ($this->currentStudents > 0) {
            throw new \DomainException('Không thể hủy lớp đã có sinh viên ghi danh.');
        }
        $this->status = TutoringClassStatus::CANCELLED;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [TutoringClassStatus::OPEN, TutoringClassStatus::FULL], true);
    }
}
