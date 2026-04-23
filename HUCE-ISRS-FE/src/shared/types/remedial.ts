import type { UserRole } from '@/shared/types/auth'

export type CohortStatus = 'draft' | 'open' | 'closed'

export type Cohort = {
  id: string
  name: string
  startDate: string
  endDate: string
  status: CohortStatus
}

export type Course = {
  id: string
  cohortId: string
  /** Môn thuộc bộ môn (mock phụ đạo, không gắn DB chính quy). */
  departmentId: string
  code: string
  name: string
}

export type Registration = {
  id: string
  studentId: string
  cohortId: string
  courseId: string
  createdAt: string
}

export type MockSystemUser = {
  id: string
  username: string
  displayName: string
  role: UserRole
  email: string
  /** Mock — không kết nối API đăng nhập thật. */
  password: string
  departmentId?: string
}

export type Department = {
  id: string
  code: string
  name: string
  headEmail: string
  headPhone: string
}

export type LecturerAssignment = {
  id: string
  cohortId: string
  courseId: string
  lecturerName: string
  lecturerEmail: string
  lecturerPhone: string
}

export type SentEmailLog = {
  id: string
  departmentId: string
  subject: string
  bodyPreview: string
  sentAt: string
  ok: boolean
}

/** Cấu hình hệ thống (mock Giai đoạn 3). */
export type AppSettings = {
  schoolName: string
  supportEmail: string
  /** Đơn giá mỗi lượt đăng ký phụ đạo (VNĐ). */
  feePerRegistration: number
  /** VAT % áp vào dòng thanh toán (0–100). */
  vatPercent: number
}

export type PaymentLine = {
  cohortId: string
  cohortName: string
  courseId: string
  courseCode: string
  courseName: string
  lecturerName: string
  studentCount: number
  unitFee: number
  subtotal: number
  vatAmount: number
  total: number
}

export type CohortStatistics = {
  cohortId: string
  /** Sinh viên có ít nhất một đăng ký trong đợt. */
  distinctStudentCount: number
  /** Số môn trong danh mục đợt (tất cả course thuộc đợt). */
  catalogCourseCount: number
  /** Số môn có ít nhất một đăng ký. */
  coursesWithRegistrationCount: number
  /** “Lớp” mock: số môn đã có phân công giảng viên (tên non-empty). */
  assignedClassCount: number
  /** Tổng tiền ước tính theo đơn giá × lượt đăng ký + VAT. */
  totalRevenue: number
}

export type RemedialDemoState = {
  cohorts: Cohort[]
  courses: Course[]
  registrations: Registration[]
  systemUsers: MockSystemUser[]
  departments: Department[]
  lecturerAssignments: LecturerAssignment[]
  emailLogs: SentEmailLog[]
  settings: AppSettings
}
