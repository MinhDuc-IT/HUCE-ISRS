# Thông báo danh sách đăng ký phụ đạo

Kính gửi Bộ môn **{{ $department->name }}**,

{{ $body ?: 'Dưới đây là danh sách sinh viên đã đăng ký học phụ đạo thuộc bộ môn.' }}

@php
    $grouped = $registrations->groupBy('subject_id');
@endphp

@foreach($grouped as $subjectRegistrations)
@php $subject = $subjectRegistrations->first()->subject; @endphp
## Môn học: {{ $subject->name }} ({{ $subject->subject_code }})

| MSSV | Họ và tên | Số tiết | Ngày đăng ký |
|:--- |:--- |:---:|:--- |
@foreach($subjectRegistrations as $registration)
| {{ $registration->user->student_code ?? '—' }} | {{ $registration->user->name }} | {{ $registration->remedial_periods }} | {{ $registration->registration_date?->format('d/m/Y H:i') }} |
@endforeach

---
@endforeach

Trân trọng,
Hệ thống quản lý học vụ.
