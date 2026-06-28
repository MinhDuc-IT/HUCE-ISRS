<?php

namespace App\Listeners;

use App\Application\Notifications\LecturerAssignmentObserver;
use App\Events\LecturerAssignedToSubject;
use App\Infrastructure\Mail\LecturerAssignmentNotification;
use App\Models\RemedialRegistration;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLecturerAssignmentEmail implements LecturerAssignmentObserver
{
    public function handle(LecturerAssignedToSubject $event): void
    {
        Log::info('[LecturerAssignmentEmail] Handling lecturer assignment event.', [
            'subject_id' => $event->subjectId,
            'department_id' => $event->departmentId,
            'lecturer_email' => $event->lecturerEmail,
            'updated_count' => $event->updatedCount,
        ]);

        try {
            $subject = Subject::query()
                ->with('department')
                ->whereKey($event->subjectId)
                ->where('department_id', $event->departmentId)
                ->where('is_deleted', false)
                ->first();

            if ($subject === null) {
                Log::warning('[LecturerAssignmentEmail] Subject not found for lecturer notification.', [
                    'subject_id' => $event->subjectId,
                    'department_id' => $event->departmentId,
                ]);

                return;
            }

            $registrations = RemedialRegistration::query()
                ->with(['user', 'remedialTerm'])
                ->where('subject_id', $event->subjectId)
                ->whereHas('subject', fn ($query) => $query
                    ->where('department_id', $event->departmentId)
                    ->where('is_deleted', false))
                ->orderBy('remedial_term_id')
                ->orderBy('registration_date')
                ->get();

            Mail::to($event->lecturerEmail)->send(new LecturerAssignmentNotification(
                subjectModel: $subject,
                registrations: $registrations,
                lecturerName: $event->lecturerName,
                lecturerPhoneNumber: $event->lecturerPhoneNumber,
                updatedCount: $event->updatedCount,
                assignedBy: $event->assignedBy,
            ));

            Log::info('[LecturerAssignmentEmail] Lecturer assignment email sent.', [
                'subject_id' => $event->subjectId,
                'department_id' => $event->departmentId,
                'lecturer_email' => $event->lecturerEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('[LecturerAssignmentEmail] Failed to send lecturer assignment email.', [
                'subject_id' => $event->subjectId,
                'department_id' => $event->departmentId,
                'lecturer_email' => $event->lecturerEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
