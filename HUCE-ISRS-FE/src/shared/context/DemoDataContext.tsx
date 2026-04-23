import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import { SEED_REMEDIAL_STATE } from '@/modules/admin/mockData/seedRemedial'
import type {
  AppSettings,
  Cohort,
  CohortStatistics,
  Course,
  Department,
  LecturerAssignment,
  MockSystemUser,
  PaymentLine,
  Registration,
  RemedialDemoState,
  SentEmailLog,
} from '@/shared/types/remedial'

const STORAGE_KEY = 'huce-isrs-demo-remedial'

function clone<T>(v: T): T {
  return JSON.parse(JSON.stringify(v)) as T
}

function normalizeLoaded(parsed: unknown): RemedialDemoState {
  const b = clone(SEED_REMEDIAL_STATE)
  if (!parsed || typeof parsed !== 'object') return b
  const p = parsed as Partial<RemedialDemoState>

  const cohorts =
    Array.isArray(p.cohorts) && p.cohorts.length > 0 ? p.cohorts : b.cohorts
  const coursesRaw =
    Array.isArray(p.courses) && p.courses.length > 0 ? p.courses : b.courses
  const courses = coursesRaw.map((c) => ({
    ...c,
    departmentId: c.departmentId ?? 'dept-1',
  }))
  const registrations = Array.isArray(p.registrations)
    ? p.registrations
    : b.registrations
  const systemUsers =
    Array.isArray(p.systemUsers) && p.systemUsers.length > 0
      ? p.systemUsers
      : b.systemUsers
  const departments =
    Array.isArray(p.departments) && p.departments.length > 0
      ? p.departments
      : b.departments
  const lecturerAssignments = Array.isArray(p.lecturerAssignments)
    ? p.lecturerAssignments
    : b.lecturerAssignments
  const emailLogs = Array.isArray(p.emailLogs) ? p.emailLogs : b.emailLogs
  const rawSettings =
    p.settings && typeof p.settings === 'object'
      ? (p.settings as Partial<AppSettings>)
      : {}
  const settings: AppSettings = {
    schoolName:
      typeof rawSettings.schoolName === 'string' && rawSettings.schoolName.trim()
        ? rawSettings.schoolName
        : b.settings.schoolName,
    supportEmail:
      typeof rawSettings.supportEmail === 'string' &&
      rawSettings.supportEmail.trim()
        ? rawSettings.supportEmail
        : b.settings.supportEmail,
    feePerRegistration: Number.isFinite(rawSettings.feePerRegistration)
      ? Math.max(0, rawSettings.feePerRegistration as number)
      : b.settings.feePerRegistration,
    vatPercent: Number.isFinite(rawSettings.vatPercent)
      ? Math.max(0, Math.min(100, rawSettings.vatPercent as number))
      : b.settings.vatPercent,
  }

  return {
    cohorts,
    courses,
    registrations,
    systemUsers,
    departments,
    lecturerAssignments,
    emailLogs,
    settings,
  }
}

function loadState(): RemedialDemoState {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY)
    if (!raw) return clone(SEED_REMEDIAL_STATE)
    return normalizeLoaded(JSON.parse(raw) as unknown)
  } catch {
    return clone(SEED_REMEDIAL_STATE)
  }
}

function saveState(s: RemedialDemoState) {
  sessionStorage.setItem(STORAGE_KEY, JSON.stringify(s))
}

function newId(prefix: string) {
  return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`
}

const DEMO_COURSE_TEMPLATES: Omit<Course, 'id' | 'cohortId'>[] = [
  { departmentId: 'dept-1', code: 'PDX1', name: 'Môn phụ đạo mẫu 1' },
  { departmentId: 'dept-1', code: 'PDX2', name: 'Môn phụ đạo mẫu 2' },
  { departmentId: 'dept-2', code: 'PDX3', name: 'Môn phụ đạo mẫu 3' },
]

type DemoDataContextValue = {
  state: RemedialDemoState
  getOpenCohortsForRegistration: () => Cohort[]
  getCoursesByCohort: (cohortId: string) => Course[]
  registrationCountForCohort: (cohortId: string) => number
  addCohort: (input: Omit<Cohort, 'id'>) => Cohort
  updateCohort: (id: string, input: Partial<Omit<Cohort, 'id'>>) => void
  removeCohort: (id: string) => { ok: true } | { ok: false; reason: 'has_registrations' }
  registerCourses: (
    studentId: string,
    cohortId: string,
    courseIds: string[],
  ) => { ok: true } | { ok: false; reason: 'cohort_closed' }
  removeRegistration: (registrationId: string, studentId: string) => boolean
  getRegistrationsForStudent: (studentId: string) => Registration[]
  getCohort: (id: string) => Cohort | undefined
  getCourse: (id: string) => Course | undefined
  getDepartment: (id: string) => Department | undefined
  updateDepartment: (
    id: string,
    input: Partial<Pick<Department, 'headEmail' | 'headPhone'>>,
  ) => void
  addSystemUser: (input: Omit<MockSystemUser, 'id'>) => MockSystemUser
  updateSystemUser: (
    id: string,
    input: Partial<Omit<MockSystemUser, 'id'>>,
  ) => void
  removeSystemUser: (id: string) => void
  getLecturerAssignment: (
    cohortId: string,
    courseId: string,
  ) => LecturerAssignment | undefined
  upsertLecturerAssignment: (
    cohortId: string,
    courseId: string,
    data: Pick<
      LecturerAssignment,
      'lecturerName' | 'lecturerEmail' | 'lecturerPhone'
    >,
  ) => void
  listCoursesWithMeta: (departmentIdFilter?: string | null) => {
    cohort: Cohort
    course: Course
    department: Department
  }[]
  sendDepartmentEmail: (input: {
    departmentId: string
    toEmail: string
    subject: string
    body: string
  }) => { ok: true } | { ok: false; reason: 'invalid_email' }
  updateSettings: (input: Partial<AppSettings>) => void
  getPaymentLines: () => PaymentLine[]
  getCohortStatistics: (cohortId: string) => CohortStatistics | null
  resetDemoData: () => void
}

const DemoDataContext = createContext<DemoDataContextValue | null>(null)

function isCohortOpenForStudent(c: Cohort): boolean {
  if (c.status !== 'open') return false
  const today = new Date().toISOString().slice(0, 10)
  return c.startDate <= today && today <= c.endDate
}

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

function buildPaymentLines(s: RemedialDemoState): PaymentLine[] {
  const feeRaw = s.settings.feePerRegistration
  const vatRaw = s.settings.vatPercent
  const fee = Number.isFinite(feeRaw) ? Math.max(0, feeRaw) : 0
  const vatRate = Number.isFinite(vatRaw) ? (Math.max(0, Math.min(100, vatRaw)) / 100) : 0
  const lines: PaymentLine[] = []
  for (const a of s.lecturerAssignments) {
    if (!a.lecturerName.trim()) continue
    const course = s.courses.find(
      (c) => c.id === a.courseId && c.cohortId === a.cohortId,
    )
    const cohort = s.cohorts.find((c) => c.id === a.cohortId)
    if (!course || !cohort) continue
    const studentCount = s.registrations.filter(
      (r) => r.cohortId === a.cohortId && r.courseId === a.courseId,
    ).length
    const subtotal = studentCount * fee
    const vatAmount = Math.round(subtotal * vatRate)
    const total = subtotal + vatAmount
    lines.push({
      cohortId: cohort.id,
      cohortName: cohort.name,
      courseId: course.id,
      courseCode: course.code,
      courseName: course.name,
      lecturerName: a.lecturerName.trim(),
      studentCount,
      unitFee: fee,
      subtotal,
      vatAmount,
      total,
    })
  }
  lines.sort((x, y) => {
    const c = x.cohortName.localeCompare(y.cohortName, 'vi')
    if (c !== 0) return c
    return x.courseCode.localeCompare(y.courseCode, 'vi')
  })
  return lines
}

function buildCohortStatistics(
  s: RemedialDemoState,
  cohortId: string,
): CohortStatistics | null {
  const cohort = s.cohorts.find((c) => c.id === cohortId)
  if (!cohort) return null
  const regs = s.registrations.filter((r) => r.cohortId === cohortId)
  const catalogCourseCount = s.courses.filter((c) => c.cohortId === cohortId)
    .length
  const distinctStudentCount = new Set(regs.map((r) => r.studentId)).size
  const coursesWithRegistrationCount = new Set(regs.map((r) => r.courseId))
    .size
  const assignedClassCount = s.lecturerAssignments.filter(
    (a) => a.cohortId === cohortId && a.lecturerName.trim() !== '',
  ).length
  const feeRaw = s.settings.feePerRegistration
  const vatRaw = s.settings.vatPercent
  const fee = Number.isFinite(feeRaw) ? Math.max(0, feeRaw) : 0
  const vatRate = Number.isFinite(vatRaw)
    ? Math.max(0, Math.min(100, vatRaw)) / 100
    : 0
  const perReg = fee + Math.round(fee * vatRate)
  const totalRevenue = regs.length * perReg
  return {
    cohortId,
    distinctStudentCount,
    catalogCourseCount,
    coursesWithRegistrationCount,
    assignedClassCount,
    totalRevenue,
  }
}

export function DemoDataProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<RemedialDemoState>(loadState)

  const setAndPersist = useCallback((next: RemedialDemoState) => {
    setState(next)
    saveState(next)
  }, [])

  const getOpenCohortsForRegistration = useCallback(() => {
    return state.cohorts.filter(isCohortOpenForStudent)
  }, [state.cohorts])

  const getCoursesByCohort = useCallback(
    (cohortId: string) => state.courses.filter((c) => c.cohortId === cohortId),
    [state.courses],
  )

  const registrationCountForCohort = useCallback(
    (cohortId: string) =>
      state.registrations.filter((r) => r.cohortId === cohortId).length,
    [state.registrations],
  )

  const addCohort = useCallback(
    (input: Omit<Cohort, 'id'>): Cohort => {
      const id = newId('cohort')
      const cohort: Cohort = { ...input, id }
      const newCourses: Course[] = DEMO_COURSE_TEMPLATES.map((t) => ({
        id: newId('course'),
        cohortId: id,
        departmentId: t.departmentId,
        code: t.code,
        name: t.name,
      }))
      setAndPersist({
        ...state,
        cohorts: [...state.cohorts, cohort],
        courses: [...state.courses, ...newCourses],
      })
      return cohort
    },
    [setAndPersist, state],
  )

  const updateCohort = useCallback(
    (id: string, input: Partial<Omit<Cohort, 'id'>>) => {
      setAndPersist({
        ...state,
        cohorts: state.cohorts.map((c) =>
          c.id === id ? { ...c, ...input } : c,
        ),
      })
    },
    [setAndPersist, state],
  )

  const removeCohort = useCallback(
    (id: string): { ok: true } | { ok: false; reason: 'has_registrations' } => {
      const count = state.registrations.filter((r) => r.cohortId === id).length
      if (count > 0) return { ok: false, reason: 'has_registrations' }
      setAndPersist({
        ...state,
        cohorts: state.cohorts.filter((c) => c.id !== id),
        courses: state.courses.filter((c) => c.cohortId !== id),
        lecturerAssignments: state.lecturerAssignments.filter(
          (a) => a.cohortId !== id,
        ),
      })
      return { ok: true }
    },
    [setAndPersist, state],
  )

  const registerCourses = useCallback(
    (
      studentId: string,
      cohortId: string,
      courseIds: string[],
    ): { ok: true } | { ok: false; reason: 'cohort_closed' } => {
      const cohort = state.cohorts.find((c) => c.id === cohortId)
      if (!cohort || !isCohortOpenForStudent(cohort)) {
        return { ok: false, reason: 'cohort_closed' }
      }
      const validIds = new Set(
        state.courses.filter((c) => c.cohortId === cohortId).map((c) => c.id),
      )
      const existingCourseIds = new Set(
        state.registrations
          .filter(
            (r) => r.studentId === studentId && r.cohortId === cohortId,
          )
          .map((r) => r.courseId),
      )
      const nextRegs: Registration[] = []
      for (const courseId of courseIds) {
        if (!validIds.has(courseId) || existingCourseIds.has(courseId)) continue
        existingCourseIds.add(courseId)
        nextRegs.push({
          id: newId('reg'),
          studentId,
          cohortId,
          courseId,
          createdAt: new Date().toISOString(),
        })
      }
      if (nextRegs.length === 0) return { ok: true }
      setAndPersist({
        ...state,
        registrations: [...state.registrations, ...nextRegs],
      })
      return { ok: true }
    },
    [setAndPersist, state],
  )

  const removeRegistration = useCallback(
    (registrationId: string, studentId: string): boolean => {
      const target = state.registrations.find((r) => r.id === registrationId)
      if (!target || target.studentId !== studentId) return false
      setAndPersist({
        ...state,
        registrations: state.registrations.filter((r) => r.id !== registrationId),
      })
      return true
    },
    [setAndPersist, state],
  )

  const getRegistrationsForStudent = useCallback(
    (studentId: string) =>
      state.registrations.filter((r) => r.studentId === studentId),
    [state.registrations],
  )

  const getCohort = useCallback(
    (id: string) => state.cohorts.find((c) => c.id === id),
    [state.cohorts],
  )

  const getCourse = useCallback(
    (id: string) => state.courses.find((c) => c.id === id),
    [state.courses],
  )

  const getDepartment = useCallback(
    (id: string) => state.departments.find((d) => d.id === id),
    [state.departments],
  )

  const updateDepartment = useCallback(
    (id: string, input: Partial<Pick<Department, 'headEmail' | 'headPhone'>>) => {
      setAndPersist({
        ...state,
        departments: state.departments.map((d) =>
          d.id === id ? { ...d, ...input } : d,
        ),
      })
    },
    [setAndPersist, state],
  )

  const addSystemUser = useCallback(
    (input: Omit<MockSystemUser, 'id'>): MockSystemUser => {
      const row: MockSystemUser = { ...input, id: newId('user') }
      setAndPersist({
        ...state,
        systemUsers: [...state.systemUsers, row],
      })
      return row
    },
    [setAndPersist, state],
  )

  const updateSystemUser = useCallback(
    (id: string, input: Partial<Omit<MockSystemUser, 'id'>>) => {
      setAndPersist({
        ...state,
        systemUsers: state.systemUsers.map((u) =>
          u.id === id ? { ...u, ...input } : u,
        ),
      })
    },
    [setAndPersist, state],
  )

  const removeSystemUser = useCallback(
    (id: string) => {
      setAndPersist({
        ...state,
        systemUsers: state.systemUsers.filter((u) => u.id !== id),
      })
    },
    [setAndPersist, state],
  )

  const getLecturerAssignment = useCallback(
    (cohortId: string, courseId: string) =>
      state.lecturerAssignments.find(
        (a) => a.cohortId === cohortId && a.courseId === courseId,
      ),
    [state.lecturerAssignments],
  )

  const upsertLecturerAssignment = useCallback(
    (
      cohortId: string,
      courseId: string,
      data: Pick<
        LecturerAssignment,
        'lecturerName' | 'lecturerEmail' | 'lecturerPhone'
      >,
    ) => {
      const existing = state.lecturerAssignments.find(
        (a) => a.cohortId === cohortId && a.courseId === courseId,
      )
      if (existing) {
        setAndPersist({
          ...state,
          lecturerAssignments: state.lecturerAssignments.map((a) =>
            a.id === existing.id ? { ...a, ...data } : a,
          ),
        })
        return
      }
      const row: LecturerAssignment = {
        id: newId('asg'),
        cohortId,
        courseId,
        ...data,
      }
      setAndPersist({
        ...state,
        lecturerAssignments: [...state.lecturerAssignments, row],
      })
    },
    [setAndPersist, state],
  )

  const listCoursesWithMeta = useCallback(
    (departmentIdFilter?: string | null) => {
      const rows: { cohort: Cohort; course: Course; department: Department }[] =
        []
      for (const course of state.courses) {
        if (departmentIdFilter && course.departmentId !== departmentIdFilter) {
          continue
        }
        const cohort = state.cohorts.find((c) => c.id === course.cohortId)
        const department = state.departments.find(
          (d) => d.id === course.departmentId,
        )
        if (!cohort || !department) continue
        rows.push({ cohort, course, department })
      }
      rows.sort((a, b) => {
        const t = a.cohort.name.localeCompare(b.cohort.name, 'vi')
        if (t !== 0) return t
        return a.course.code.localeCompare(b.course.code, 'vi')
      })
      return rows
    },
    [state.cohorts, state.courses, state.departments],
  )

  const sendDepartmentEmail = useCallback(
    (input: {
      departmentId: string
      toEmail: string
      subject: string
      body: string
    }): { ok: true } | { ok: false; reason: 'invalid_email' } => {
      if (!EMAIL_RE.test(input.toEmail.trim())) {
        return { ok: false, reason: 'invalid_email' }
      }
      const log: SentEmailLog = {
        id: newId('mail'),
        departmentId: input.departmentId,
        subject: input.subject.trim(),
        bodyPreview: input.body.trim().slice(0, 200),
        sentAt: new Date().toISOString(),
        ok: true,
      }
      setAndPersist({
        ...state,
        emailLogs: [log, ...state.emailLogs],
      })
      return { ok: true }
    },
    [setAndPersist, state],
  )

  const updateSettings = useCallback(
    (input: Partial<AppSettings>) => {
      setAndPersist({
        ...state,
        settings: { ...state.settings, ...input },
      })
    },
    [setAndPersist, state],
  )

  const getPaymentLines = useCallback(
    () => buildPaymentLines(state),
    [state],
  )

  const getCohortStatistics = useCallback(
    (cohortId: string) => buildCohortStatistics(state, cohortId),
    [state],
  )

  const resetDemoData = useCallback(() => {
    const next = clone(SEED_REMEDIAL_STATE)
    setState(next)
    saveState(next)
  }, [])

  const value = useMemo(
    () => ({
      state,
      getOpenCohortsForRegistration,
      getCoursesByCohort,
      registrationCountForCohort,
      addCohort,
      updateCohort,
      removeCohort,
      registerCourses,
      removeRegistration,
      getRegistrationsForStudent,
      getCohort,
      getCourse,
      getDepartment,
      updateDepartment,
      addSystemUser,
      updateSystemUser,
      removeSystemUser,
      getLecturerAssignment,
      upsertLecturerAssignment,
      listCoursesWithMeta,
      sendDepartmentEmail,
      updateSettings,
      getPaymentLines,
      getCohortStatistics,
      resetDemoData,
    }),
    [
      state,
      getOpenCohortsForRegistration,
      getCoursesByCohort,
      registrationCountForCohort,
      addCohort,
      updateCohort,
      removeCohort,
      registerCourses,
      removeRegistration,
      getRegistrationsForStudent,
      getCohort,
      getCourse,
      getDepartment,
      updateDepartment,
      addSystemUser,
      updateSystemUser,
      removeSystemUser,
      getLecturerAssignment,
      upsertLecturerAssignment,
      listCoursesWithMeta,
      sendDepartmentEmail,
      updateSettings,
      getPaymentLines,
      getCohortStatistics,
      resetDemoData,
    ],
  )

  return (
    <DemoDataContext.Provider value={value}>{children}</DemoDataContext.Provider>
  )
}

export function useDemoData(): DemoDataContextValue {
  const ctx = useContext(DemoDataContext)
  if (!ctx) throw new Error('useDemoData must be used within DemoDataProvider')
  return ctx
}
