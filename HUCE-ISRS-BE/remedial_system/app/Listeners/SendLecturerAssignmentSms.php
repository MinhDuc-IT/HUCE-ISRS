<?php

namespace App\Listeners;

use App\Application\Notifications\LecturerAssignmentObserver;
use App\Events\LecturerAssignedToSubject;
use Illuminate\Support\Facades\Log;

class SendLecturerAssignmentSms implements LecturerAssignmentObserver
{
    public function handle(LecturerAssignedToSubject $event): void
    {
        Log::info('[SendLecturerAssignmentSms] SMS notification observer invoked.', [
            'subject_id' => $event->subjectId,
            'department_id' => $event->departmentId,
            'lecturer_phone_number' => $event->lecturerPhoneNumber,
            'lecturer_email' => $event->lecturerEmail,
        ]);

        // TODO: Nếu muốn gửi SMS thật, gắn adapter SMS ở đây.
        // Ví dụ: $this->smsClient->send($event->lecturerPhoneNumber, $message);
    }
}
