<x-mail::message>
# Thông báo phân công giảng viên phụ đạo

Kính gửi {{ $lecturerName ?: 'Giảng viên' }},

Thầy/cô đã được phân công phụ đạo môn <strong>{{ $subjectModel->name }}</strong> ({{ $subjectModel->subject_code }}).

Khoa/bộ môn: <strong>{{ $subjectModel->department?->name ?? 'Chưa xác định' }}</strong><br>
Số đăng ký được phân công: <strong>{{ $updatedCount }}</strong>

@if($lecturerPhoneNumber)
Số điện thoại liên hệ: {{ $lecturerPhoneNumber }}<br>
@endif

@if($assignedBy)
Người phân công: {{ $assignedBy }}<br>
@endif

<br>
@if($registrations->isNotEmpty())
Danh sách sinh viên chi tiết đã được đính kèm trong file Excel.
@else
Hiện chưa có sinh viên nào được phân vào môn phụ đạo này.
@endif

<br>
Trân trọng,<br>
Hệ thống quản lý học vụ.
</x-mail::message>
