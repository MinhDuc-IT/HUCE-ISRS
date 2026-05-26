<?php

namespace App\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DepartmentRemedialSummary extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $department,
        public $registrations,
        public $emailSubject,
        public $emailBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject ?: "Danh sách đăng ký phụ đạo - {$this->department->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.departments.remedial_summary',
            with: [
                'department'    => $this->department,
                'registrations' => $this->registrations,
                'body'          => $this->emailBody,
            ],
        );
    }
}
