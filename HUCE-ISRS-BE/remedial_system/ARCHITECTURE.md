# Kiến trúc remedial_system (Hexagonal)

remedial_system/
├── app/
│   ├── Domain/                          # LÕI – PHP thuần, không Laravel
│   │   ├── Entities/
│   │   │   ├── Department.php
│   │   │   ├── RemedialRegistration.php      # đổi từ TutoringRequest
│   │   │   ├── RemedialTerm.php              # đổi từ TutoringTerm
│   │   │   ├── Subject.php                   # catalog local (đổi từ Course)
│   │   │   ├── Student.php
│   │   │   ├── StudentInfo.php               # từ API trường
│   │   │   ├── SubjectResult.php             # đổi từ CourseResult
│   │   │   ├── SystemConfiguration.php
│   │   │   └── User.php                      # optional: profile domain
│   │   ├── Enums/
│   │   │   ├── SystemConfigKey.php
│   │   │   └── UserRole.php                  # admin | bo_mon | sinh_vien
│   │   ├── Exceptions/
│   │   │   ├── StudentNotFoundException.php
│   │   │   ├── ExternalSystemException.php
│   │   │   └── DomainException.php           # base business errors
│   │   ├── Specifications/
│   │   │   ├── Specification.php
│   │   │   ├── SubjectEligibleForRemedial.php
│   │   │   └── StudentEligibleForRemedial.php
│   │   └── Ports/                            # OUTBOUND – chỉ interface
│   │       ├── Persistence/
│   │       │   ├── DepartmentRepositoryPort.php
│   │       │   ├── RemedialRegistrationRepositoryPort.php
│   │       │   ├── RemedialTermRepositoryPort.php
│   │       │   ├── SubjectRepositoryPort.php
│   │       │   ├── StudentRepositoryPort.php
│   │       │   ├── SystemConfigurationRepositoryPort.php
│   │       │   └── UserRepositoryPort.php
│   │       └── External/
│   │           └── StudentInfoPort.php
│   │
│   ├── Application/                     # USE CASES
│   │   ├── Services/
│   │   │   ├── Auth/
│   │   │   │   └── AuthenticateUserService.php
│   │   │   ├── Admin/
│   │   │   │   ├── ManageUserService.php
│   │   │   │   ├── ManageDepartmentService.php
│   │   │   │   ├── ManageRemedialTermService.php
│   │   │   │   ├── ManageSubjectService.php
│   │   │   │   ├── ManageSystemConfigurationService.php
│   │   │   │   └── RemedialStatisticsService.php
│   │   │   ├── Department/
│   │   │   │   ├── DepartmentProfileService.php
│   │   │   │   └── DepartmentRegistrationQueryService.php
│   │   │   ├── Student/
│   │   │   │   ├── StudentProvisioningService.php
│   │   │   │   ├── StudentSyncService.php
│   │   │   │   └── RemedialRegistrationService.php
│   │   │   └── Shared/
│   │   │       └── SendDepartmentSummaryEmailService.php
│   │   └── DTO/                            # optional – input/output use case
│   │       ├── CreateRemedialTermData.php
│   │       └── RegisterRemedialSubjectData.php
│   │
│   ├── Infrastructure/                  # ADAPTER RA – implement Ports
│   │   ├── Persistence/
│   │   │   ├── Eloquent/
│   │   │   │   ├── Repositories/
│   │   │   │   │   ├── EloquentDepartmentRepository.php
│   │   │   │   │   ├── EloquentRemedialRegistrationRepository.php
│   │   │   │   │   ├── EloquentRemedialTermRepository.php
│   │   │   │   │   ├── EloquentSubjectRepository.php
│   │   │   │   │   ├── EloquentStudentRepository.php
│   │   │   │   │   ├── EloquentSystemConfigurationRepository.php
│   │   │   │   │   └── EloquentUserRepository.php
│   │   │   │   └── Mappers/                # Model ↔ Entity
│   │   │   │       ├── RemedialTermMapper.php
│   │   │   │       ├── RemedialRegistrationMapper.php
│   │   │   │       └── ...
│   │   │   └── ...
│   │   ├── External/
│   │   │   └── University/
│   │   │       ├── UniversityAuthClient.php
│   │   │       ├── StudentInfoApiAdapter.php
│   │   │       └── CachedStudentInfoAdapter.php
│   │   ├── Mail/
│   │   │   └── DepartmentRemedialSummary.php   # chuyển từ app/Mail
│   │   └── Support/
│   │       └── CircuitBreaker.php
│   │
│   ├── Http/                            # PRIMARY ADAPTER – API
│   │   ├── Controllers/
│   │   │   ├── BaseController.php            # envelope JSON (giữ)
│   │   │   ├── Swagger.php
│   │   │   └── Api/
│   │   │       ├── Auth/
│   │   │       │   └── AuthController.php
│   │   │       ├── Admin/
│   │   │       │   ├── UserController.php
│   │   │       │   ├── DepartmentController.php
│   │   │       │   ├── RemedialTermController.php
│   │   │       │   ├── SubjectController.php
│   │   │       │   ├── SystemConfigurationController.php
│   │   │       │   ├── RemedialRegistrationController.php  # list admin
│   │   │       │   └── StatisticsController.php
│   │   │       ├── Department/
│   │   │       │   ├── ProfileController.php
│   │   │       │   └── RegistrationController.php
│   │   │       └── Student/
│   │   │           ├── EligibleSubjectController.php
│   │   │           └── RemedialRegistrationController.php
│   │   ├── Middleware/
│   │   │   └── EnsureUserHasRole.php
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   ├── Admin/
│   │   │   ├── Department/
│   │   │   └── Student/
│   │   └── Resources/                    # JsonResource – snake_case
│   │       ├── UserResource.php
│   │       ├── RemedialTermResource.php
│   │       ├── RemedialRegistrationResource.php
│   │       └── ...
│   │
│   ├── Models/                          # Eloquent – CHỈ Infrastructure dùng
│   │   ├── User.php
│   │   ├── Department.php
│   │   ├── RemedialTerm.php
│   │   ├── RemedialRegistration.php
│   │   ├── Subject.php
│   │   ├── Student.php
│   │   └── SystemConfiguration.php
│   │
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── RemedialServiceProvider.php   # bind Port → Adapter
│
├── routes/
│   └── api.php                           # group admin | department | student
├── database/
│   ├── migrations/
│   └── seeders/
└── config/
    └── remedial.php

## Luồng request

```
Route → Middleware (auth:sanctum, role) → Controller → FormRequest
  → Application\Service → Domain\Ports\* → Infrastructure → Models
  → JsonResource (snake_case) → BaseController::success()
```

## Phân quyền

| Middleware | Mô tả |
|------------|--------|
| *(không)* | Public — chỉ `POST /auth/login` |
| `auth:sanctum` | Đã đăng nhập (mọi role) |
| `role:admin` | `users.role = admin` |
| `role:bo_mon` | Trưởng / bộ môn |
| `role:sinh_vien` | Sinh viên |

Cột `*` trong bảng API = mọi role đã đăng nhập.

## Bảng API đầy đủ

Prefix chung: `/api`. Response envelope: `{ success, message, data, errors }` (snake_case trong `data`).

### Public – Auth

| Method | Path | Role | Mô tả |
|--------|------|------|--------|
| POST | `/auth/login` | — | Đăng nhập (email/password hoặc `student_code`) |

### Auth (đã đăng nhập)

| Method | Path | Role | Mô tả |
|--------|------|------|--------|
| POST | `/auth/logout` | * | Thu hồi token |
| GET | `/auth/me` | * | Thông tin user + `home_url` |

### Admin

| Method | Path | Role | Mô tả |
|--------|------|------|--------|
| GET | `/admin/system-configurations` | admin | Danh sách cấu hình |
| POST | `/admin/system-configurations` | admin | Cập nhật cấu hình (bulk) |
| GET | `/admin/statistics/terms` | admin | Danh sách đợt + thống kê tóm tắt |
| GET | `/admin/statistics/terms/{id}` | admin | Thống kê chi tiết một đợt |
| GET | `/admin/users` | admin | Danh sách người dùng |
| POST | `/admin/users` | admin | Tạo người dùng |
| GET | `/admin/users/{id}` | admin | Chi tiết người dùng |
| PATCH | `/admin/users/{user}` | admin | Cập nhật người dùng |
| DELETE | `/admin/users/{user}` | admin | Xóa mềm người dùng |
| GET | `/admin/remedial-terms` | admin | Danh sách đợt phụ đạo |
| POST | `/admin/remedial-terms` | admin | Tạo đợt |
| GET | `/admin/remedial-terms/{id}` | admin | Chi tiết đợt |
| PATCH | `/admin/remedial-terms/{id}` | admin | Cập nhật đợt |
| DELETE | `/admin/remedial-terms/{id}` | admin | Xóa mềm đợt |
| GET | `/admin/remedial-terms/{id}/statistics` | admin | Thống kê theo đợt (alias) |
| GET | `/admin/departments` | admin | Danh sách bộ môn |
| POST | `/admin/departments` | admin | Tạo bộ môn |
| GET | `/admin/departments/{id}` | admin | Chi tiết bộ môn |
| PATCH | `/admin/departments/{id}` | admin | Cập nhật bộ môn |
| DELETE | `/admin/departments/{id}` | admin | Xóa mềm bộ môn |
| POST | `/admin/departments/{id}/send-email` | admin | Gửi email tổng hợp cho BM |
| GET | `/admin/subjects` | admin | Danh sách học phần |
| POST | `/admin/subjects` | admin | Tạo học phần |
| GET | `/admin/subjects/{id}` | admin | Chi tiết học phần |
| PATCH | `/admin/subjects/{id}` | admin | Cập nhật học phần |
| DELETE | `/admin/subjects/{id}` | admin | Xóa mềm học phần |
| GET | `/admin/remedial-registrations` | admin | Tra cứu đăng ký (`?remedial_term_id=`) |

### Department (`bo_mon`)

| Method | Path | Role | Mô tả |
|--------|------|------|--------|
| GET | `/department/me` | bo_mon | Hồ sơ bộ môn đăng nhập |
| PATCH | `/department/me` | bo_mon | Cập nhật hồ sơ |
| GET | `/department/remedial-registrations` | bo_mon | Đăng ký thuộc BM |
| PATCH | `/department/remedial-registrations/{id}` | bo_mon | Gán GV phụ đạo / liên hệ |
| POST | `/department/send-summary-email` | bo_mon | Gửi email tổng hợp (BM) |

### Student (`sinh_vien`)

| Method | Path | Role | Mô tả |
|--------|------|------|--------|
| GET | `/student/me/eligible-subjects` | sinh_vien | Môn đủ điều kiện (nợ/hỏng) |
| GET | `/student/me/remedial-registrations` | sinh_vien | Đăng ký của SV |
| POST | `/student/me/remedial-registrations` | sinh_vien | Đăng ký môn |
| DELETE | `/student/me/remedial-registrations/{id}` | sinh_vien | Hủy đăng ký |
| GET | `/student/remedial-terms/current` | sinh_vien | Đợt phụ đạo hiện tại |

OpenAPI: `php artisan l5-swagger:generate` → `/api/documentation`

## Envelope JSON

```json
{ "success": true|false, "message": "...", "data": ..., "errors": ... }
```

## Cấu trúc `app/`

- `Application/Services/` — Auth, Admin, Department, Student
- `Domain/Ports/` — repository & external interfaces
- `Infrastructure/` — Eloquent, University API
- `Http/Controllers/{Api,Admin,Department,Student,OpenApi}/`

## DI & routes

`app/Providers/RemedialServiceProvider.php` — bindings Domain/Application.  
Routes chỉ load từ `bootstrap/app.php` → `routes/api.php`.

## Database

- Migrations: `database/migrations/`
- Schema tham khảo: `database/remedial.sql`
- Seed: `php artisan db:seed` (admin, bộ môn CNTT, system config)

## Kiểm thử (Phase 11)

```bash
php artisan test
```

| Test | Phạm vi |
|------|---------|
| `tests/Feature/AuthTest.php` | Login admin/SV, `/auth/me`, sai mật khẩu |
| `tests/Feature/RemedialTermTest.php` | CRUD đợt (admin), 403 cho `bo_mon` |
| `tests/Feature/StudentRegistrationTest.php` | Đăng ký, hủy, eligible subjects |

Tests dùng SQLite in-memory; University System được mock qua `tests/Support/FakeStudentInfoPort.php`.
