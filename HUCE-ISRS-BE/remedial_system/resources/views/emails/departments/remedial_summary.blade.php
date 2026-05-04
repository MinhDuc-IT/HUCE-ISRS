# Thông báo danh sách môn học phụ đạo

Kính gửi Bộ môn **{{ $department->DepartmentName }}**,

{{ $body ?: 'Dưới đây là danh sách các môn học được mở lớp phụ đạo thuộc sự quản lý của Bộ môn, kèm theo danh sách sinh viên đã đăng ký.' }}

@foreach($tutoringClasses as $class)
## Môn học: {{ $class->course->CourseName }} ({{ $class->course->CourseCode }})
**Giảng viên:** {{ $class->teacher ? $class->teacher->FullName : 'Chưa phân công' }}

| MSSV | Họ và tên | Trạng thái |
|:--- |:--- |:--- |
@foreach($class->enrollments as $enrollment)
| {{ $enrollment->student->StudentCode }} | {{ $enrollment->student->FullName }} | {{ $enrollment->Status }} |
@endforeach

---
@endforeach

Trân trọng,
Hệ thống quản lý học vụ.

