<?php

namespace App\Domain\States\TutoringRequest;

class PaidState extends RequestState
{
    public function approve(): void {}
    public function reject(string $reason): void {}
    public function pay(): void {}
}
