# Đăng ký phụ đạo (một hoặc nhiều môn)

**API:** `POST /api/student/me/remedial-registrations`  
**Body:** `{ "course_codes": ["CS101", "MATH201"] }` hoặc `{ "course_code": "CS101" }`  
**FE:** `StudentRegisterPage`

```mermaid
sequenceDiagram
  autonumber
  participant FE as StudentRegisterPage
  participant Ctrl as Student/RemedialRegistrationController
  participant Req as StoreRemedialRegistrationRequest
  participant RegSvc as RemedialRegistrationService
  participant TermPort as RemedialTermRepositoryPort
  participant StuPort as StudentInfoPort
  participant SubPort as SubjectRepositoryPort
  participant RegPort as RemedialRegistrationRepositoryPort
  participant Uni as University API
  participant DB as remedial DB

  FE->>Ctrl: POST course_codes[] + Bearer
  Ctrl->>Req: authorize sinh_vien + rules
  Ctrl->>RegSvc: bulkRegisterForUser(user, codes)

  RegSvc->>TermPort: findCurrent()
  TermPort->>DB: remedial_terms
  RegSvc->>RegSvc: term.isRegistrationOpen()
  Note over RegSvc: registration_end = endOfDay
  alt Cửa đăng ký đóng
    RegSvc-->>FE: 400 "Không trong thời gian đăng ký"
  end

  RegSvc->>StuPort: getRegisteredCoursesForSemester(code, year, semester)
  StuPort->>Uni: registered-courses
  Uni-->>RegSvc: TermRegisteredCourse[]

  loop Từng course_code
    Note over RegSvc: Mã có trong danh sách kỳ?
    alt Không thuộc kỳ
      RegSvc-->>FE: 400 môn không hợp lệ
    end
    RegSvc->>SubPort: findByCode(code)
    SubPort->>DB: subjects
    alt Chưa sync catalog
      RegSvc-->>FE: 404 môn chưa có trong hệ thống
    end
    RegSvc->>RegPort: exists(student, term, subject)?
    alt Đã đăng ký
      RegSvc-->>FE: 409 hoặc bỏ qua (theo implement)
    end
    RegSvc->>RegPort: save(RemedialRegistration)
    RegPort->>DB: INSERT remedial_registrations
  end

  RegSvc-->>Ctrl: registrations[]
  Ctrl-->>FE: 201 { success, data }
```
