<?php

namespace App\Application\Notifications;

use App\Events\LecturerAssignedToSubject;
use Illuminate\Support\Facades\Log;

class LecturerAssignmentNotificationDispatcher
{
    /**
     * @param iterable<LecturerAssignmentObserver> $observers
     */
    public function __construct(
        private readonly iterable $observers,
    ) {}

    public function handle(LecturerAssignedToSubject $event): void
    {
        foreach ($this->observers as $observer) {
            try {
                $observer->handle($event);
            } catch (\Throwable $e) {
                Log::error('[LecturerAssignmentNotificationDispatcher] Observer failed', [
                    'observer' => $observer::class,
                    'event' => LecturerAssignedToSubject::class,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
