<?php

namespace App\Application\Notifications;

use App\Events\LecturerAssignedToSubject;

interface LecturerAssignmentObserver
{
    public function handle(LecturerAssignedToSubject $event): void;
}
