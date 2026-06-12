# Gán giảng viên phụ đạo theo môn (bulk)

**API:** `PATCH /api/department/subjects/{subjectId}/lecturer`  
**Body:** `{ "lecture_name", "lecturer_phone_number", "lecturer_email" }`  
**FE:** `DepartmentAssignmentsPage` (modal)

```mermaid
sequenceDiagram
  autonumber
  participant FE as DepartmentAssignmentsPage
  participant Ctrl as SubjectAssignmentController
  participant Req as UpdateRegistrationLecturerRequest
  participant Mgmt as DepartmentManageRegistrationService
  participant SubPort as SubjectRepositoryPort
  participant TermPort as RemedialTermRepositoryPort
  participant RegPort as RemedialRegistrationRepositoryPort
  participant Eloquent as EloquentRemedialRegistrationRepository
  participant DB as remedial DB

  FE->>Ctrl: PATCH + Bearer
  Ctrl->>Req: validate bo_mon
  Ctrl->>Mgmt: updateLecturerForSubject(user, subjectId, data)

  Mgmt->>SubPort: findById(subjectId)
  SubPort->>DB: subjects
  alt subject.department_id ≠ user.department_id
    Mgmt-->>FE: 403 / 404
  end

  Mgmt->>Mgmt: assertRegistrationPeriodClosed(subjectId)
  Mgmt->>TermPort: các remedial_terms có đăng ký môn này
  loop Từng term
    Mgmt->>Mgmt: term.isRegistrationOpen()
    alt Vẫn trong cửa đăng ký
      Mgmt-->>FE: 400 "Chưa hết thời gian đăng ký"
    end
  end

  Mgmt->>RegPort: bulkUpdateLecturerForSubject(subjectId, departmentId, lecturerData)
  RegPort->>Eloquent: UPDATE remedial_registrations
  Eloquent->>DB: SET lecture_name, lecturer_phone, lecturer_email<br/>WHERE subject_id AND department filter
  DB-->>Mgmt: updated_count

  Mgmt-->>Ctrl: { updated_count }
  Ctrl-->>FE: 200 { success, data }
```
