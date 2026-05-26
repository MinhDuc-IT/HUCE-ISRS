import type { AuthUser, UserRole } from '@/shared/types/auth'
import { getHomePathForRole } from '@/shared/utils/rolePaths'

type ApiAuthUser = {
  id: number | string
  name: string
  email?: string | null
  role: string
  student_code?: string | null
  department_id?: number | null
  home_url?: string | null
}

export function mapApiUserToAuthUser(apiUser: ApiAuthUser): AuthUser {
  let role: UserRole = 'student'
  if (apiUser.role === 'admin') role = 'admin'
  else if (apiUser.role === 'bo_mon') role = 'department'
  else if (apiUser.role === 'sinh_vien') role = 'student'
  else if (apiUser.role === 'department' || apiUser.role === 'student') {
    role = apiUser.role as UserRole
  }

  return {
    id: String(apiUser.id),
    username: apiUser.student_code || apiUser.email || String(apiUser.id),
    displayName: apiUser.name,
    role,
    departmentId: apiUser.department_id != null ? String(apiUser.department_id) : undefined,
    homeUrl: apiUser.home_url || getHomePathForRole(role),
  }
}

export function resolveRedirectPath(user: AuthUser): string {
  return user.homeUrl || getHomePathForRole(user.role)
}
