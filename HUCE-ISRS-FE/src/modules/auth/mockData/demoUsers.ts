import type { AuthUser, UserRole } from '@/shared/types/auth'

export type DemoCredential = {
  username: string
  password: string
  user: AuthUser
}

/** Tài khoản demo — Giai đoạn 0 (UI + mock đăng nhập) */
export const DEMO_USERS: DemoCredential[] = [
  {
    username: 'admin',
    password: 'admin',
    user: {
      id: 'u-admin',
      username: 'admin',
      displayName: 'Quản trị viên',
      role: 'admin',
    },
  },
  {
    username: 'bomon',
    password: 'bomon',
    user: {
      id: 'u-dept',
      username: 'bomon',
      displayName: 'Trưởng bộ môn',
      role: 'department',
      departmentId: 'dept-1',
    },
  },
  {
    username: 'sinhvien',
    password: 'sinhvien',
    user: {
      id: 'u-student',
      username: 'sinhvien',
      displayName: 'Sinh viên demo',
      role: 'student',
    },
  },
]

export function findDemoUser(
  username: string,
  password: string,
): AuthUser | null {
  const row = DEMO_USERS.find(
    (c) => c.username === username && c.password === password,
  )
  return row ? row.user : null
}

export function getHomePathForRole(role: UserRole): string {
  switch (role) {
    case 'admin':
      return '/admin'
    case 'department':
      return '/department'
    case 'student':
      return '/student'
  }
}
