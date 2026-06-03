# Thong bao phan cong giang vien phu dao

Kinh gui {{ $lecturerName ?: 'Giang vien' }},

Thay/co da duoc phan cong phu dao mon **{{ $subjectModel->name }}** ({{ $subjectModel->subject_code }}).

Bo mon: **{{ $subjectModel->department?->name ?? 'Chua xac dinh' }}**

So dang ky duoc phan cong: **{{ $updatedCount }}**

@if($lecturerPhoneNumber)
So dien thoai lien he: {{ $lecturerPhoneNumber }}
@endif

@if($assignedBy)
Nguoi phan cong: {{ $assignedBy }}
@endif

@if($registrations->isNotEmpty())
## Danh sach sinh vien dang ky

| MSSV | Ho va ten | Dot phu dao | So tiet | Ngay dang ky |
|:--- |:--- |:--- |:---:|:--- |
@foreach($registrations as $registration)
| {{ $registration->user?->student_code ?? '-' }} | {{ $registration->user?->name ?? '-' }} | {{ $registration->remedialTerm?->name ?? '-' }} | {{ $registration->remedial_periods }} | {{ $registration->registration_date?->format('d/m/Y H:i') ?? '-' }} |
@endforeach
@endif

Tran trong,
He thong quan ly hoc vu.
