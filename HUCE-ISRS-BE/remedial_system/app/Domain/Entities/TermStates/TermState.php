<?php

namespace App\Domain\Entities\TermStates;

use App\Domain\Enums\RemedialTermStatus;

interface TermState
{
    /**
     * @return RemedialTermStatus
     */
    public function getStatus(): RemedialTermStatus;

    /**
     * Trạng thái kế tiếp mặc định của state này, nếu có.
     */
    public function nextStatus(): ?RemedialTermStatus;

    /**
     * @throws \DomainException
     */
    public function transitionTo(RemedialTermStatus $status): void;

    /**
     * Validate if the incoming update data is allowed in this state.
     * @throws \DomainException
     */
    public function validateUpdate(array $data): void;

    /**
     * @throws \DomainException
     */
    public function openRegistration(): void;

    /**
     * @throws \DomainException
     */
    public function activate(): void;

    /**
     * @throws \DomainException
     */
    public function complete(): void;

    /**
     * @throws \DomainException
     */
    public function cancel(): void;
}
