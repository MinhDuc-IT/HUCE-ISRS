import { Navigate } from 'react-router-dom'
import { getHomePathForRole } from '@/modules/auth/mockData/demoUsers'
import { useAuth } from '@/shared/context/AuthContext'

/** `/` → dashboard theo vai trò hoặc trang đăng nhập. */
export function HomeRedirect() {
  const { user } = useAuth()
  if (!user) return <Navigate to="/login" replace />
  return <Navigate to={getHomePathForRole(user.role)} replace />
}
