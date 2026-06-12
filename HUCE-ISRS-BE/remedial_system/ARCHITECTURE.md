# Kiến trúc remedial_system (Hexagonal)

## Tổng quan

- **Backend:** Laravel API — envelope JSON `{ success, message, data, errors }`, field `data` dùng **snake_case**.
- **Frontend:** `HUCE-ISRS-FE` gọi `http://127.0.0.1:8000/api` qua `apiFetch`.
- **University System:** adapter `StudentInfoPort` — điểm, môn đăng ký chính quy theo kỳ, xác thực SV.

```
Route → Middleware (auth:sanctum, role) → Controller → FormRequest
  → Application\Service → Domain\Ports → Infrastructure → Eloquent
  → Presenter / array DTO → BaseController::success()
```

## Sơ đồ luồng request (Hexagonal)

Client
  ↓
Route
  ↓
Middleware
  ↓
Controller
  ↓
FormRequest (validate)
  ↓
Application Service (Use Case)
  ↓
Domain Entity + Domain Rules
  ↓
Port interface
  ↓
Repository Adapter / External API Adapter
  ↓
Database / External System

### 1. Các lớp và hướng phụ thuộc

| Lớp | Thư mục | Vai trò trong 1 request |
|-----|---------|-------------------------|
| Primary adapter | `Http/` | Nhận HTTP, validate, gọi use case, trả JSON envelope |
| Application | `Application/Services/` | Orchestrate: gọi Port, áp dụng quy tắc entity |
| Domain | `Domain/` | Entity + interface Port; không biết Laravel/HTTP |
| Secondary adapter | `Infrastructure/` | Thực thi Port: SQL, HTTP University |
| Models | `app/Models/` | Chi tiết bảng; chỉ Infrastructure chạm |

### 3. DI — Port gắn implementation ở đâu

`RemedialServiceProvider` đăng ký khi boot; Controller/Service chỉ type-hint **interface**:

## Cây thư mục `app/` (Hexagonal)

```
remedial_system/
└── app/
    ├── Domain/                              # LÕI – PHP thuần, không phụ thuộc Laravel
    │   ├── Entities/                        # Đối tượng nghiệp vụ (immutable / readonly)
    │   │   ├── Department.php
    │   │   ├── RemedialRegistration.php
    │   │   ├── RemedialTerm.php
    │   │   ├── Subject.php
    │   │   ├── Student.php
    │   │   ├── StudentInfo.php              # Dữ liệu SV từ University (ACL)
    │   │   ├── SubjectResult.php            # Kết quả học tập từ University
    │   │   ├── TermRegisteredCourse.php     # Môn ĐK chính quy theo kỳ (University)
    │   │   └── SystemConfiguration.php
    │   ├── Enums/
    │   │   └── SystemConfigKey.php
    │   ├── Exceptions/                      # Lỗi domain (không phải HTTP)
    │   │   ├── AccountDeactivatedException.php
    │   │   ├── ExternalSystemException.php
    │   │   ├── InvalidCredentialsException.php
    │   │   └── StudentNotFoundException.php
    │   ├── Specifications/                # Quy tắc nghiệp vụ tái sử dụng
    │   │   ├── Specification.php
    │   │   ├── SubjectEligibleForRemedial.php
    │   │   └── StudentEligibleForRemedial.php
    │   └── Ports/                           # OUTBOUND – chỉ interface (hợp đồng)
    │       ├── Persistence/
    │       │   ├── DepartmentRepositoryPort.php
    │       │   ├── RemedialRegistrationRepositoryPort.php   # ghi
    │       │   ├── RemedialRegistrationQueryPort.php          # đọc / báo cáo
    │       │   ├── RemedialTermRepositoryPort.php
    │       │   ├── SubjectRepositoryPort.php
    │       │   ├── StudentRepositoryPort.php
    │       │   ├── SystemConfigurationRepositoryPort.php
    │       │   └── UserRepositoryPort.php
    │       └── External/
    │           └── StudentInfoPort.php        # University System API
    │
    ├── Application/                         # USE CASES – điều phối nghiệp vụ
    │   └── Services/
    │       ├── Auth/
    │       │   ├── AuthenticateUserService.php
    │       │   └── AuthUserPresenter.php
    │       ├── Admin/
    │       │   ├── ManageUserService.php
    │       │   ├── ManageDepartmentService.php
    │       │   ├── ManageRemedialTermService.php
    │       │   ├── ManageSubjectService.php
    │       │   ├── ManageSystemConfigurationService.php
    │       │   ├── RemedialStatisticsService.php
    │       │   └── AdminRemedialRegistrationQueryService.php
    │       ├── Department/
    │       │   ├── DepartmentProfileService.php
    │       │   ├── DepartmentRegistrationQueryService.php
    │       │   ├── DepartmentSubjectAssignmentQueryService.php
    │       │   ├── DepartmentManageRegistrationService.php
    │       │   └── SendDepartmentSummaryEmailService.php
    │       ├── RemedialRegistrationService.php      # SV: đăng ký / hủy / môn theo kỳ
    │       ├── StudentProvisioningService.php       # Auto-provision SV lần đầu
    │       ├── StudentSyncService.php                 # Sync subjects từ University
    │       ├── StudentRegistrationPresenter.php       # Format response đăng ký
    │       └── DepartmentService.php                  # (legacy / hỗ trợ)
    │
    ├── Infrastructure/                      # ADAPTER RA – implement Ports
    │   ├── Persistence/
    │   │   └── Eloquent/
    │   │       ├── Repositories/              # *Repository implements *Port
    │   │       │   ├── EloquentDepartmentRepository.php
    │   │       │   ├── EloquentRemedialRegistrationRepository.php
    │   │       │   ├── EloquentRemedialRegistrationQueryRepository.php
    │   │       │   ├── EloquentRemedialTermRepository.php
    │   │       │   ├── EloquentSubjectRepository.php
    │   │       │   ├── EloquentStudentRepository.php
    │   │       │   ├── EloquentSystemConfigurationRepository.php
    │   │       │   └── EloquentUserRepository.php
    │   │       └── Mappers/                   # Eloquent Model ↔ Domain Entity
    │   │           ├── DepartmentMapper.php
    │   │           ├── RemedialRegistrationMapper.php
    │   │           ├── RemedialTermMapper.php
    │   │           ├── SubjectMapper.php
    │   │           └── SystemConfigurationMapper.php
    │   ├── External/
    │   │   └── University/
    │   │       ├── UniversityAuthClient.php
    │   │       ├── StudentInfoApiAdapter.php      # StudentInfoPort
    │   │       └── CachedStudentInfoAdapter.php   # Decorator cache
    │   ├── Mail/
    │   │   └── DepartmentRemedialSummary.php
    │   └── Support/
    │       └── CircuitBreaker.php
    │
    ├── Http/                                # PRIMARY ADAPTER – API HTTP
    │   ├── Controllers/
    │   │   ├── BaseController.php             # envelope success/error
    │   │   ├── Controller.php
    │   │   ├── Swagger.php / OpenApi.php
    │   │   ├── Api/
    │   │   │   └── AuthController.php
    │   │   ├── Admin/
    │   │   │   ├── UserController.php
    │   │   │   ├── DepartmentController.php
    │   │   │   ├── RemedialTermController.php
    │   │   │   ├── SubjectController.php
    │   │   │   ├── SystemConfigurationController.php
    │   │   │   ├── RemedialRegistrationController.php
    │   │   │   └── StatisticsController.php
    │   │   ├── Department/
    │   │   │   ├── ProfileController.php
    │   │   │   ├── RemedialRegistrationController.php
    │   │   │   ├── SubjectAssignmentController.php
    │   │   │   └── SummaryEmailController.php
    │   │   ├── Student/
    │   │   │   ├── RemedialRegistrationController.php
    │   │   │   ├── TermRegisteredSubjectController.php
    │   │   │   ├── EligibleSubjectController.php
    │   │   │   └── RemedialTermController.php
    │   │   └── OpenApi/
    │   │       └── RemedialApiPaths.php
    │   ├── Middleware/
    │   │   └── EnsureUserHasRole.php
    │   ├── Requests/                          # Validate input (FormRequest)
    │   │   ├── ApiFormRequest.php
    │   │   ├── Auth/
    │   │   ├── Admin/
    │   │   ├── Department/
    │   │   └── Student/
    │   └── Resources/                         # JSON snake_case ra client
    │       ├── ApiResource.php
    │       ├── UserResource.php
    │       ├── DepartmentResource.php
    │       ├── RemedialTermResource.php
    │       ├── SubjectResource.php
    │       └── SystemConfigurationResource.php
    │
    ├── Models/                              # Eloquent – CHỈ Infrastructure dùng
    │   ├── User.php
    │   ├── Department.php
    │   ├── RemedialTerm.php
    │   ├── RemedialRegistration.php
    │   ├── Subject.php
    │   ├── Student.php
    │   ├── SystemConfiguration.php
    │   └── Scopes/
    │       └── NotDeletedScope.php
    │
    └── Providers/
        ├── AppServiceProvider.php
        └── RemedialServiceProvider.php        # bind Port → Adapter (DI)
```

### Vai trò từng tầng (Hexagonal)

| Tầng | Vị trí | Nhiệm vụ | Không được |
|------|--------|----------|------------|
| **Domain** | Trung tâm | Entity, quy tắc (`RemedialTerm::isRegistrationOpen`), Port (interface), Exception domain | Import `Illuminate\*`, Eloquent, HTTP |
| **Application** | Use case | Gọi Port, orchestrate luồng (đăng ký, gán GV, CRUD admin); không biết SQL/HTTP | Truy vấn Eloquent trực tiếp |
| **Infrastructure** | Adapter ra | Implement Port: DB (Eloquent + Mapper), University API, Mail, CircuitBreaker | Chứa rule nghiệp vụ phức tạp |
| **Http** | Adapter vào | Route → Middleware → Controller → Request → Service → Resource/Presenter | Business logic dày trong Controller |
| **Models** | Chi tiết DB | Bảng `remedial_*`, quan hệ, global scope `is_deleted` | Dùng trực tiếp từ Controller (đi qua Repository) |
| **Providers** | Composition | `RemedialServiceProvider`: `Port` → `Eloquent*`, `StudentInfoPort` → Adapter + Cache | — |

### Chi tiết folder `Domain/`

| Folder | Nhiệm vụ |
|--------|----------|
| `Entities/` | Mô hình nghiệp vụ thuần: đợt phụ đạo, đăng ký, môn, SV. `StudentInfo` / `SubjectResult` / `TermRegisteredCourse` là anti-corruption từ University. |
| `Ports/Persistence/` | Hợp đồng lưu/đọc DB. Tách `RemedialRegistrationQueryPort` (read model) khỏi `RemedialRegistrationRepositoryPort` (write). |
| `Ports/External/` | Hợp đồng gọi hệ thống ngoài (`StudentInfoPort` → University). |
| `Exceptions/` | Lỗi có ý nghĩa nghiệp vụ; Controller map sang HTTP status. |
| `Specifications/` | Điều kiện “đủ điều kiện phụ đạo” (môn trượt, v.v.). |
| `Enums/` | Hằng cấu hình (`SystemConfigKey`). |

### Chi tiết folder `Application/Services/`

| Folder / file | Nhiệm vụ |
|---------------|----------|
| `Auth/` | Login staff/SV, logout, payload `/auth/me`. |
| `Admin/` | CRUD user, department, remedial term, subject, settings; thống kê đợt; tra cứu đăng ký admin. |
| `Department/` | Hồ sơ BM; list đăng ký; **phân công GV theo môn**; gửi email tổng hợp. |
| `RemedialRegistrationService.php` | Use case SV: đăng ký/hủy; lấy môn theo kỳ (`term-registered-subjects`). |
| `StudentProvisioningService.php` | Tạo user SV lần đầu sau khi University xác thực. |
| `StudentSyncService.php` | Đồng bộ `subjects` từ `getCourses()` khi provision. |
| `StudentRegistrationPresenter.php` | Map `RemedialRegistration` entity → array API. |

### Chi tiết folder `Infrastructure/`

| Folder | Nhiệm vụ |
|--------|----------|
| `Persistence/Eloquent/Repositories/` | Implement từng `*RepositoryPort` / `RemedialRegistrationQueryPort`. |
| `Persistence/Eloquent/Mappers/` | Chuyển `Model` ↔ `Domain\Entity` (tránh leak Eloquent vào Application). |
| `External/University/` | HTTP client University: token, `StudentInfoApiAdapter`, bọc `CachedStudentInfoAdapter`. |
| `Mail/` | Template email tổng hợp phụ đạo cho bộ môn. |
| `Support/` | `CircuitBreaker` chống cascade khi University lỗi. |

### Chi tiết folder `Http/`

| Folder | Nhiệm vụ |
|--------|----------|
| `Controllers/{Api,Admin,Department,Student}/` | Entry point REST; gọi một Application Service; trả `BaseController::success()`. |
| `Middleware/` | `EnsureUserHasRole` — `admin` \| `bo_mon` \| `sinh_vien`. |
| `Requests/` | Validation + authorize theo role; chuẩn hóa input snake_case. |
| `Resources/` | Serialize entity/array ra JSON response (snake_case). |

### Ghi / đọc tách repository

| Port | Implement | Vai trò |
|------|-----------|---------|
| `RemedialRegistrationRepositoryPort` | `EloquentRemedialRegistrationRepository` | Entity: save, delete, bulk gán GV theo `subject_id` |
| `RemedialRegistrationQueryPort` | `EloquentRemedialRegistrationQueryRepository` | Query: list admin/BM, **group theo môn** (`listSubjectAssignmentSummaries`) |

## Phân quyền

| Middleware | Mô tả |
|------------|--------|
| *(không)* | Public — `POST /auth/login` |
| `auth:sanctum` | Đã đăng nhập |
| `role:admin` | Quản trị |
| `role:bo_mon` | Bộ môn |
| `role:sinh_vien` | Sinh viên |

## Bảng API (`/api` + …)

Cột **FE** = trang/module frontend đang gọi (tháng 05/2026).

### Public – Auth

| Method | Path | FE | Mô tả |
|--------|------|-----|--------|
| POST | `/auth/login` | `LoginPage`, `AuthContext` | Email/password hoặc `student_code` + password (University) |

### Auth (đã đăng nhập)

| Method | Path | FE | Mô tả |
|--------|------|-----|--------|
| POST | `/auth/logout` | `AuthContext` | Thu hồi token |
| GET | `/auth/me` | `AuthContext` | User + `home_url` |

### Admin

| Method | Path | FE | Mô tả |
|--------|------|-----|--------|
| GET | `/admin/system-configurations` | `SystemSettingsPage` | Danh sách cấu hình |
| POST | `/admin/system-configurations` | `SystemSettingsPage` | Cập nhật bulk |
| GET | `/admin/statistics/terms` | `AdminDashboardPage`, `StatisticsCohortPage` | Đợt + thống kê tóm tắt |
| GET | `/admin/statistics/terms/{id}` | `AdminDashboardPage`, `StatisticsCohortPage` | Chi tiết một đợt |
| GET/POST | `/admin/users` | `UserListPage`, `UserFormPage` | CRUD user |
| GET/PATCH/DELETE | `/admin/users/{id\|user}` | `UserFormPage`, `UserListPage` | Chi tiết / sửa / xóa mềm |
| GET/POST/PATCH/DELETE | `/admin/remedial-terms` | `CohortListPage`, `CohortFormPage` | CRUD đợt phụ đạo |
| GET | `/admin/remedial-terms/{id}` | `CohortFormPage` | Chi tiết đợt |
| GET | `/admin/remedial-terms/{id}/statistics` | — | Alias thống kê đợt |
| GET/POST/PATCH/DELETE | `/admin/departments` | `DepartmentListPage` | CRUD bộ môn (seed HUCE) |
| POST | `/admin/departments/{id}/send-email` | `AdminSendEmailPage` | Email tổng hợp cho BM |
| GET/POST/PATCH/DELETE | `/admin/subjects` | — | CRUD học phần catalog (BE; FE chưa có màn) |
| GET | `/admin/remedial-registrations` | `AdminRegistrationsPage` | Tra cứu (`?remedial_term_id=`) |

### Department (`bo_mon`)

| Method | Path | FE | Mô tả |
|--------|------|-----|--------|
| GET | `/department/me` | `DepartmentProfilePage` | Hồ sơ BM |
| PATCH | `/department/me` | `DepartmentProfilePage` | Cập nhật email/SĐT |
| GET | `/department/remedial-registrations` | `DepartmentDashboardPage` | Đăng ký theo từng SV (chi tiết) |
| PATCH | `/department/remedial-registrations/{id}` | — | Gán GV **một** đơn (legacy; assignments dùng API theo môn) |
| GET | `/department/subject-assignments` | `DepartmentAssignmentsPage` | Môn có ĐK phụ đạo, **group theo `subject`** |
| PATCH | `/department/subjects/{subjectId}/lecturer` | `DepartmentAssignmentsPage` | Gán GV cho **mọi** `remedial_registrations` của môn; chỉ sau `registration_end` |
| POST | `/department/send-summary-email` | — | Gửi email tổng hợp (BM) |

**Phân công GV (luồng hiện tại):**

1. FE: `GET /department/subject-assignments` → `subject_code`, `department_name`, `credits`, `registration_count`, `lecture_name`, `lecturer_phone`, `lecturer_email`, `can_assign_lecturer`.
2. `can_assign_lecturer = true` khi mọi đợt liên quan đã **hết** cửa đăng ký (`RemedialTerm::isRegistrationOpen()` = false; ngày đóng tính đến **cuối ngày**).
3. FE modal → `PATCH /department/subjects/{id}/lecturer` body: `lecture_name`, `lecturer_phone_number`, `lecturer_email`.

Lọc BM: chỉ môn có `subjects.department_id` = `users.department_id`. Môn sync lần đầu từ SV có thể thuộc `DEFAULT` — cần gán lại `department_id` (admin/SQL).

### Student (`sinh_vien`)

| Method | Path | FE | Mô tả |
|--------|------|-----|--------|
| GET | `/student/me/term-registered-subjects` | `StudentRegisterPage` | Môn **đã ĐK chính quy** trùng `year`/`semester` đợt phụ đạo hiện tại (University) |
| GET | `/student/me/remedial-registrations` | `StudentRegisterPage`, `StudentRegistrationsPage`, `StudentInstructorsPage`, `StudentDashboardPage` | Đăng ký phụ đạo của SV |
| POST | `/student/me/remedial-registrations` | `StudentRegisterPage` | Body: `course_code` hoặc `course_codes[]` |
| DELETE | `/student/me/remedial-registrations/{id}` | `StudentRegisterPage`, `StudentRegistrationsPage` | Hủy đăng ký |
| GET | `/student/remedial-terms/current` | `AppShellLayout`, `StudentDashboardPage` | Đợt phụ đạo `is_current_term` |
| GET | `/student/me/eligible-subjects` | — | Môn **trượt** từ bảng điểm (BE giữ; FE đăng ký không dùng nữa) |

**Đăng ký phụ đạo (luồng hiện tại):**

1. SV chọn môn từ `term-registered-subjects` (không phải `eligible-subjects`).
2. `RemedialRegistrationService::registerForUser` kiểm tra môn thuộc danh sách kỳ, cửa đăng ký mở, môn có trong `subjects` (sync lúc provision).
3. Mapping đợt: `remedial_terms.year` ↔ `DM_NamHoc.NamHoc`, `remedial_terms.semester` ↔ `DM_Dot.SoThuTu`.

**Provision SV lần đầu:**

- `StudentProvisioningService::findOrProvision` → `StudentSyncService` gọi University `getCourses`, `updateOrCreate` `subjects` (chỉ gán `department_id` = DEFAULT khi **tạo mới**).

## University System (external)

| Port method | University API | Dùng cho |
|-------------|----------------|----------|
| `getStudent` | `GET /api/students/{code}` | Login / provision |
| `getCourses` | `GET /api/students/{code}/courses` | Sync `subjects` |
| `getRegisteredCoursesForSemester` | `GET /api/students/{code}/registered-courses/{year}/{semester}` | `term-registered-subjects` |
| `verifyCredentials` | `POST /api/student/login` | Login SV |

Cache: `CachedStudentInfoAdapter` (TTL ~1h); key version `v2` cho registered courses sau khi thêm `plannedClass`.

## Envelope JSON

```json
{ "success": true, "message": "...", "data": { ... }, "errors": null }
```

## Database

- Migrations: `database/migrations/`
- Schema tham khảo: `database/remedial.sql`
- Seed: `php artisan db:seed` — admin, bộ môn HUCE (`huce_departments.php`), system config, user BM mẫu (`department_code` = `54`)

## OpenAPI

```bash
php artisan l5-swagger:generate
```

→ `/api/documentation`

## Kiểm thử

```bash
php artisan test
```

| File | Phạm vi |
|------|---------|
| `tests/Feature/AuthTest.php` | Login admin/SV, `/auth/me` |
| `tests/Feature/RemedialTermTest.php` | CRUD đợt (admin) |
| `tests/Feature/StudentRegistrationTest.php` | Đăng ký/hủy, `term-registered-subjects`, cửa đăng ký cuối ngày |
| `tests/Feature/DepartmentRegistrationTest.php` | `subject-assignments`, bulk lecturer, chặn trước `registration_end` |
| `tests/Unit/RemedialTermRegistrationWindowTest.php` | `isRegistrationOpen()` inclusive end date |

Tests: SQLite in-memory; University mock — `tests/Support/FakeStudentInfoPort.php`.

## DI

Bindings: `app/Providers/RemedialServiceProvider.php`  
Routes: `bootstrap/app.php` → `routes/api.php`

## Ánh xạ FE ↔ route (quick reference)

| Route FE | API chính |
|----------|-----------|
| `/admin/*` | `/admin/*` |
| `/department/profile` | `GET/PATCH /department/me` |
| `/department/assignments` | `GET subject-assignments`, `PATCH subjects/{id}/lecturer` |
| `/student/register` | `term-registered-subjects`, `remedial-registrations` POST |
| `/student/registrations` | `remedial-registrations` GET/DELETE |
| `/student/instructors` | `remedial-registrations` GET (có `lecture_name`, …) |
