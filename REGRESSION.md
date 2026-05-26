# Regression — Phase 10 (3 vai trò)

Chạy BE: `cd HUCE-ISRS-BE/remedial_system && php artisan serve`  
Chạy FE: `cd HUCE-ISRS-FE && npm run dev`

## Admin

| # | Luồng | Kỳ vọng |
|---|--------|---------|
| 1 | Đăng nhập email + password | Redirect `/admin`, `home_url` = `/admin` |
| 2 | Đợt phụ đạo — list / thêm / sửa / xóa | API `GET/POST/PATCH/DELETE /admin/remedial-terms` |
| 3 | Người dùng — CRUD | `/admin/users` |
| 4 | Bộ môn — CRUD | `/admin/departments`, payload `department_code`, `name` |
| 5 | Cài đặt | `GET/POST /admin/system-configurations` |
| 6 | Tra cứu đăng ký | `GET /admin/remedial-registrations?remedial_term_id=` |
| 7 | Gửi email BM | `POST /admin/departments/{id}/send-email` |
| 8 | Thống kê đợt | `GET /admin/statistics/terms`, `.../terms/{id}` — field snake_case |

## Bộ môn (`bo_mon`)

| # | Luồng | Kỳ vọng |
|---|--------|---------|
| 1 | Đăng nhập | Redirect `/department` |
| 2 | Hồ sơ | `GET/PATCH /department/me` |
| 3 | Đăng ký thuộc BM | `GET /department/remedial-registrations` |
| 4 | Sửa GV phụ đạo | `PATCH /department/remedial-registrations/{id}` |
| 5 | Không gọi được `/admin/*` | 403 |

## Sinh viên

| # | Luồng | Kỳ vọng |
|---|--------|---------|
| 1 | Đăng nhập `student_code` | Redirect `/student` |
| 2 | Đăng ký / hủy môn | `POST/DELETE /student/me/remedial-registrations` |
| 3 | Môn đủ điều kiện | `GET /student/me/eligible-subjects` — `course_code`, `subject_name` |
| 4 | Môn đã đăng ký / GV | Trang registrations & instructors dùng API, không mock |
| 5 | Đợt hiện tại (banner) | `GET /student/remedial-terms/current` |

## Đã gỡ (không còn trong menu)

- Phân công GV admin (`/admin/tutoring-classes`) — mock
- Thanh toán (`/admin/payments`) — mock
- Route deprecated: `/registrations`, `/students/{id}/*`
