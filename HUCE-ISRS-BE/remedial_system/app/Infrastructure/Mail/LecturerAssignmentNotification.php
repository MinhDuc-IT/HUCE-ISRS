<?php

namespace App\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LecturerAssignmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $subjectModel,
        public $registrations,
        public ?string $lecturerName,
        public ?string $lecturerPhoneNumber,
        public int $updatedCount,
        public ?string $assignedBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Phan cong giang vien phu dao - {$this->subjectModel->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.lecturers.assignment_notification',
            with: [
                'subjectModel' => $this->subjectModel,
                'registrations' => $this->registrations,
                'lecturerName' => $this->lecturerName,
                'lecturerPhoneNumber' => $this->lecturerPhoneNumber,
                'updatedCount' => $this->updatedCount,
                'assignedBy' => $this->assignedBy,
            ],
        );
    }
}
