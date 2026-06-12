# Sequence diagrams — remedial_system

Sơ đồ trình tự (Mermaid) theo **từng chức năng**. Xem bằng preview Markdown hoặc [mermaid.live](https://mermaid.live).

## Chung

| File | Mô tả |
|------|--------|
| [00-generic-request-flow.md](./00-generic-request-flow.md) | Luồng Hexagonal chung cho mọi API |

## Auth

| File | API | FE |
|------|-----|-----|
| [auth-login-staff.md](./auth-login-staff.md) | `POST /auth/login` (email) | `LoginPage` |
| [auth-login-student.md](./auth-login-student.md) | `POST /auth/login` (student_code) | `LoginPage` |
| [auth-logout.md](./auth-logout.md) | `POST /auth/logout` | `AuthContext` |
| [auth-me.md](./auth-me.md) | `GET /auth/me` | `AuthContext` |

## Sinh viên

| File | API | FE |
|------|-----|-----|
| [student-remedial-term-current.md](./student-remedial-term-current.md) | `GET /student/remedial-terms/current` | Banner, dashboard |
| [student-term-registered-subjects.md](./student-term-registered-subjects.md) | `GET /student/me/term-registered-subjects` | `StudentRegisterPage` |
| [student-remedial-registration-list.md](./student-remedial-registration-list.md) | `GET /student/me/remedial-registrations` | Register, registrations, instructors |
| [student-remedial-registration-create.md](./student-remedial-registration-create.md) | `POST /student/me/remedial-registrations` | `StudentRegisterPage` |
| [student-remedial-registration-cancel.md](./student-remedial-registration-cancel.md) | `DELETE /student/me/remedial-registrations/{id}` | Register, registrations |
| [student-eligible-subjects.md](./student-eligible-subjects.md) | `GET /student/me/eligible-subjects` | *(BE only, FE không dùng)* |

## Bộ môn

| File | API | FE |
|------|-----|-----|
| [department-profile.md](./department-profile.md) | `GET/PATCH /department/me` | `DepartmentProfilePage` |
| [department-remedial-registrations-list.md](./department-remedial-registrations-list.md) | `GET /department/remedial-registrations` | `DepartmentDashboardPage` |
| [department-subject-assignments-list.md](./department-subject-assignments-list.md) | `GET /department/subject-assignments` | `DepartmentAssignmentsPage` |
| [department-assign-lecturer-by-subject.md](./department-assign-lecturer-by-subject.md) | `PATCH /department/subjects/{id}/lecturer` | Modal gán GV |

## Admin

| File | API | FE |
|------|-----|-----|
| [admin-remedial-terms-list.md](./admin-remedial-terms-list.md) | `GET /admin/remedial-terms` | `CohortListPage`, registrations filter |
| [admin-remedial-term-detail.md](./admin-remedial-term-detail.md) | `GET /admin/remedial-terms/{id}` | `CohortFormPage` |
| [admin-remedial-term-create-update.md](./admin-remedial-term-create-update.md) | `POST/PATCH /admin/remedial-terms` | `CohortFormPage` |
| [admin-remedial-term-delete.md](./admin-remedial-term-delete.md) | `DELETE /admin/remedial-terms/{id}` | `CohortListPage` |
| [admin-users.md](./admin-users.md) | CRUD `/admin/users` | `UserListPage`, `UserFormPage` |
| [admin-departments.md](./admin-departments.md) | CRUD `/admin/departments` | `DepartmentListPage` |
| [admin-remedial-registrations.md](./admin-remedial-registrations.md) | `GET /admin/remedial-registrations` | `AdminRegistrationsPage` |
| [admin-system-configurations.md](./admin-system-configurations.md) | `GET/POST /admin/system-configurations` | `SystemSettingsPage` |
| [admin-statistics-terms.md](./admin-statistics-terms.md) | `GET /admin/statistics/terms` | Dashboard, statistics |
| [admin-statistics-term-detail.md](./admin-statistics-term-detail.md) | `GET /admin/statistics/terms/{id}` | Dashboard, statistics |
| [admin-send-department-email.md](./admin-send-department-email.md) | `POST /admin/departments/{id}/send-email` | `AdminSendEmailPage` |
