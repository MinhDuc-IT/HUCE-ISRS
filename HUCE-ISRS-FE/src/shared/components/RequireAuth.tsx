import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import type { UserRole } from '@/shared/types/auth'

type RequireAuthProps = {
  allowedRoles: UserRole[]
}

/**
 * Bảo vệ route: bắt buộc đăng nhập + đúng vai trò (Giai đoạn 0).
 */
export function RequireAuth({ allowedRoles }: RequireAuthProps) {
  const { user } = useAuth()

  if (!user) {
    return <Navigate to="/login" replace />
  }

  if (!allowedRoles.includes(user.role)) {
    return <Navigate to="/" replace />
  }

  return <Outlet />
}
