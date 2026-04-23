import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import { findDemoUser, getHomePathForRole } from '@/modules/auth/mockData/demoUsers'
import type { AuthUser } from '@/shared/types/auth'

const STORAGE_KEY = 'huce-isrs-demo-auth'

type AuthContextValue = {
  user: AuthUser | null
  login: (username: string, password: string) => AuthUser | null
  logout: () => void
}

const AuthContext = createContext<AuthContextValue | null>(null)

function readStoredUser(): AuthUser | null {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY)
    if (!raw) return null
    return JSON.parse(raw) as AuthUser
  } catch {
    return null
  }
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(() => readStoredUser())

  const login = useCallback((username: string, password: string) => {
    const next = findDemoUser(username.trim(), password)
    if (next) {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(next))
      setUser(next)
    }
    return next
  }, [])

  const logout = useCallback(() => {
    sessionStorage.removeItem(STORAGE_KEY)
    setUser(null)
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

export { getHomePathForRole }
