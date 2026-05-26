import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import type { AuthUser } from '@/shared/types/auth'
import { apiFetch } from '@/shared/utils/apiClient'
import { mapApiUserToAuthUser } from '@/shared/utils/authUserMapper'

type AuthContextValue = {
  user: AuthUser | null
  login: (username: string, password: string) => Promise<AuthUser>
  logout: () => void
  refreshUser: () => Promise<AuthUser | null>
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

  const refreshUser = useCallback(async (): Promise<AuthUser | null> => {
    const token = localStorage.getItem('token')
    if (!token) return null

    try {
      const response = await apiFetch<{ data: Parameters<typeof mapApiUserToAuthUser>[0] }>(
        '/auth/me',
      )
      const authUser = mapApiUserToAuthUser(response.data)
      localStorage.setItem('user', JSON.stringify(authUser))
      setUser(authUser)
      return authUser
    } catch {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      setUser(null)
      return null
    }
  }, [])

  useEffect(() => {
    if (localStorage.getItem('token')) {
      refreshUser()
    }
  }, [refreshUser])

  const login = useCallback(async (username: string, password: string) => {
    const isEmail = username.includes('@')
    const payload = isEmail
      ? { email: username, password }
      : { student_code: username, password }

    const response = await apiFetch<{
      data: { token: string; user: Parameters<typeof mapApiUserToAuthUser>[0] }
    }>('/auth/login', {
      data: payload,
    })

    if (!response.data?.token) {
      throw new Error('Đăng nhập thất bại.')
    }

    localStorage.setItem('token', response.data.token)

    const authUser = mapApiUserToAuthUser(response.data.user)
    localStorage.setItem('user', JSON.stringify(authUser))
    setUser(authUser)
    return authUser
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
      refreshUser,
    }),
    [user, login, logout, refreshUser],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
