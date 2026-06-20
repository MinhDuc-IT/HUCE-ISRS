<?php

namespace App\Infrastructure\Mail;

use App\Infrastructure\Exports\LecturerAssignmentRegistrationsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

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
            subject: "Phân công giảng viên phụ đạo - {$this->subjectModel->name}",
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

    public function attachments(): array
    {
        if ($this->registrations->isEmpty()) {
            return [];
        }

        return [
            Attachment::fromData(
                fn () => Excel::raw(
                    new LecturerAssignmentRegistrationsExport($this->registrations),
                    ExcelWriter::XLSX,
                ),
                'danh_sach_sinh_vien_phu_dao.xlsx',
            )->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
