<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/** Domain entity – đợt phụ đạo ({@see remedial_terms}). */
class RemedialTerm
{
    public function __construct(
        public readonly ?int    $id,
        public readonly int     $year,
        public readonly int     $semester,
        public readonly string  $name,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly int     $remedialCoefficient = 1,
        public readonly int     $pricePerPeriod = 150000,
        public readonly float   $priceCoefficient = 1.0,
        public readonly bool    $isCurrentTerm = false,
        public readonly ?Carbon $registrationStart = null,
        public readonly ?Carbon $registrationEnd = null,
        public readonly \App\Domain\Enums\RemedialTermStatus $status = \App\Domain\Enums\RemedialTermStatus::DRAFT,
    ) {}

    public function getState(): \App\Domain\Entities\TermStates\TermState
    {
        return match ($this->status) {
            \App\Domain\Enums\RemedialTermStatus::DRAFT => new \App\Domain\Entities\TermStates\DraftState($this),
            \App\Domain\Enums\RemedialTermStatus::REGISTRATION_OPEN => new \App\Domain\Entities\TermStates\RegistrationOpenState($this),
            \App\Domain\Enums\RemedialTermStatus::ACTIVE => new \App\Domain\Entities\TermStates\ActiveState($this),
            \App\Domain\Enums\RemedialTermStatus::COMPLETED => new \App\Domain\Entities\TermStates\CompletedState($this),
            \App\Domain\Enums\RemedialTermStatus::CANCELLED => new \App\Domain\Entities\TermStates\CancelledState($this),
        };
    }

    public function validateUpdate(array $data): void
    {
        $this->getState()->validateUpdate($data);
    }

    public function openRegistration(): void
    {
        $this->getState()->openRegistration();
    }

    public function activate(): void
    {
        $this->getState()->activate();
    }

    public function complete(): void
    {
        $this->getState()->complete();
    }

    public function cancel(): void
    {
        $this->getState()->cancel();
    }

    public function transitionTo(\App\Domain\Enums\RemedialTermStatus $status): self
    {
        $state = $this->getState();
        $state->transitionTo($status);

        return $this->withStatus($status);
    }

    public function nextStatus(): ?\App\Domain\Enums\RemedialTermStatus
    {
        return $this->getState()->nextStatus();
    }

    public function withStatus(\App\Domain\Enums\RemedialTermStatus $status): self
    {
        return new self(
            $this->id, $this->year, $this->semester, $this->name, $this->startDate, $this->endDate,
            $this->remedialCoefficient, $this->pricePerPeriod, $this->priceCoefficient, $this->isCurrentTerm,
            $this->registrationStart, $this->registrationEnd, $status
        );
    }

    public function isRegistrationOpen(): bool
    {
        $now = Carbon::now();

        // Admin/FE gửi ngày (YYYY-MM-DD) → DB thường là 00:00:00; coi cả ngày lịch là hợp lệ.
        if ($this->registrationStart !== null) {
            $openFrom = $this->registrationStart->copy()->startOfDay();
            if ($now->lt($openFrom)) {
                return false;
            }
        }

        if ($this->registrationEnd !== null) {
            $openUntil = $this->registrationEnd->copy()->endOfDay();
            if ($now->gt($openUntil)) {
                return false;
            }
        }

        return true;
    }



    public function getLogicStatus(): \App\Domain\Enums\RemedialTermLogicStatus
    {
        $now = Carbon::now();

        if ($this->status === \App\Domain\Enums\RemedialTermStatus::DRAFT) {
            return \App\Domain\Enums\RemedialTermLogicStatus::DRAFT;
        }

        if ($this->status === \App\Domain\Enums\RemedialTermStatus::COMPLETED) {
            return \App\Domain\Enums\RemedialTermLogicStatus::COMPLETED;
        }

        if ($this->status === \App\Domain\Enums\RemedialTermStatus::CANCELLED) {
            return \App\Domain\Enums\RemedialTermLogicStatus::CANCELLED;
        }

        if ($this->endDate && $now->gt($this->endDate->copy()->endOfDay())) {
            return \App\Domain\Enums\RemedialTermLogicStatus::ACTIVE_ENDED;
        }

        if ($this->registrationStart && $this->registrationEnd) {
            if ($now->lessThan($this->registrationStart)) {
                return \App\Domain\Enums\RemedialTermLogicStatus::ACTIVE_PENDING_REGISTRATION;
            }

            if ($now->greaterThanOrEqualTo($this->registrationStart) && $now->lessThanOrEqualTo($this->registrationEnd->copy()->endOfDay())) {
                return \App\Domain\Enums\RemedialTermLogicStatus::REGISTRATION_OPEN;
            }
        }

        if ($this->registrationEnd && $now->gt($this->registrationEnd->copy()->endOfDay())) {
            if ($this->startDate && $now->lt($this->startDate->copy()->startOfDay())) {
                return \App\Domain\Enums\RemedialTermLogicStatus::ACTIVE_PENDING_CLASS;
            }
            return \App\Domain\Enums\RemedialTermLogicStatus::ACTIVE_IN_PROGRESS;
        }

        return \App\Domain\Enums\RemedialTermLogicStatus::ACTIVE_IN_PROGRESS;
    }

    public function isLogicCurrentTerm(): bool
    {
        return in_array($this->status, [
            \App\Domain\Enums\RemedialTermStatus::REGISTRATION_OPEN,
            \App\Domain\Enums\RemedialTermStatus::ACTIVE
        ], true);
    }
}
