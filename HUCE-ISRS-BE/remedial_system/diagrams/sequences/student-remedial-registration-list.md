# Danh sách đăng ký phụ đạo của sinh viên

**API:** `GET /api/student/me/remedial-registrations`  
**FE:** `StudentRegisterPage`, `StudentRegistrationsPage`, `StudentInstructorsPage`, `StudentDashboardPage`

```mermaid
sequenceDiagram
  autonumber
  participant FE as StudentRegistrationsPage
  participant Ctrl as Student/RemedialRegistrationController
  participant RegSvc as RemedialRegistrationService
  participant TermPort as RemedialTermRepositoryPort
  participant RegPort as RemedialRegistrationRepositoryPort
  participant Presenter as StudentRegistrationPresenter
  participant DB as remedial DB

  FE->>Ctrl: GET + Bearer
  Ctrl->>RegSvc: listForUser(user)
  RegSvc->>TermPort: findCurrent()
  TermPort->>DB: remedial_terms
  RegSvc->>RegPort: listByStudentAndTerm(userId, termId)
  RegPort->>DB: JOIN subjects, remedial_registrations
  DB-->>RegPort: rows
  RegPort-->>RegSvc: RemedialRegistration[]
  loop Mỗi registration
    RegSvc->>Presenter: present(registration, subject)
    Presenter-->>RegSvc: snake_case (lecture_name, status, ...)
  end
  RegSvc-->>Ctrl: array
  Ctrl-->>FE: 200 { success, data: registrations[] }
```
