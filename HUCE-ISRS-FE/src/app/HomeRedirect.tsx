import { Navigate } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { resolveRedirectPath } from '@/shared/utils/authUserMapper'

/** `/` → dashboard theo vai trò hoặc trang đăng nhập. */
export function HomeRedirect() {
  const { user } = useAuth()
  if (!user) return <Navigate to="/login" replace />
  return <Navigate to={resolveRedirectPath(user)} replace />
}
