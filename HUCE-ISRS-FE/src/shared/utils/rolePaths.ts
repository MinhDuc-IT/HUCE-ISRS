import type { UserRole } from '@/shared/types/auth'

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
