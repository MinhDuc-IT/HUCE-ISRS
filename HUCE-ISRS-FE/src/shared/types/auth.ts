export type UserRole = 'admin' | 'department' | 'student'

export type AuthUser = {
  id: string
  username: string
  displayName: string
  role: UserRole
  /** Bộ môn quản lý (chỉ role `department`) — dùng lọc phân công / hồ sơ. */
  departmentId?: string
}
