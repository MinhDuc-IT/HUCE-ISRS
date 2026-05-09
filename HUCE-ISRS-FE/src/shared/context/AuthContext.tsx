import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import type { AuthUser, UserRole } from '@/shared/types/auth'
import { apiFetch } from '@/shared/utils/apiClient'

type AuthContextValue = {
  user: AuthUser | null
  login: (username: string, password: string) => Promise<AuthUser | null>
  logout: () => void
}

const AuthContext = createContext<AuthContextValue | null>(null)

function readStoredUser(): AuthUser | null {
  try {
    const raw = localStorage.getItem('user')
    if (!raw) return null
    return JSON.parse(raw) as AuthUser
  } catch {
    return null
  }
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(() => readStoredUser())

  const login = useCallback(async (username: string, password: string) => {
    try {
      const isEmail = username.includes('@')
      const payload = isEmail 
        ? { email: username, password } 
        : { student_code: username, password }

      const response = await apiFetch<{ data: { token: string, user: any } }>('/auth/login', {
        data: payload
      })

      if (response.data && response.data.token) {
        localStorage.setItem('token', response.data.token)
        
        // Map backend user to frontend AuthUser
        let mappedRole: UserRole = 'student'
        if (response.data.user.role === 'admin') mappedRole = 'admin'
        else if (response.data.user.role === 'bo_mon') mappedRole = 'department'
        else if (response.data.user.role === 'sinh_vien') mappedRole = 'student'
        else mappedRole = response.data.user.role as UserRole

        const authUser: AuthUser = {
          id: response.data.user.id.toString(),
          username: response.data.user.student_code || response.data.user.email,
          displayName: response.data.user.name,
          role: mappedRole,
          departmentId: response.data.user.department_id?.toString(),
          homeUrl: response.data.user.home_url
        }
        
        localStorage.setItem('user', JSON.stringify(authUser))
        setUser(authUser)
        return authUser
      }
      return null
    } catch (error) {
      console.error('Login failed:', error)
      return null
    }
  }, [])

  const logout = useCallback(async () => {
    try {
      if (localStorage.getItem('token')) {
        await apiFetch('/auth/logout', { method: 'POST' }).catch(() => {})
      }
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      setUser(null)
    }
  }, [])

  const value = useMemo(
    () => ({
      user,
      login,
      logout,
    }),
    [user, login, logout],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
