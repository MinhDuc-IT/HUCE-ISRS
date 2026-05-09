<?php

namespace App\Domain\States\TutoringRequest;

use App\Domain\Entities\TutoringRequest;

abstract class RequestState
{
    public function __construct(protected TutoringRequest $context) {}

    abstract public function approve(): void;
    abstract public function reject(string $reason): void;
    abstract public function pay(): void;
    
    public function toString(): string
    {
        return static::class;
    }
}
