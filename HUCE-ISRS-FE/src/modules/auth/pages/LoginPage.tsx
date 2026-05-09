import { useState, type FormEvent } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { getHomePathForRole } from '@/modules/auth/mockData/demoUsers'
import { useAuth } from '@/shared/context/AuthContext'
import { CenteredCardLayout } from '@/shared/layouts/AppShellLayout'

export function LoginPage() {
  const { user, login } = useAuth()
  const navigate = useNavigate()

  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  if (user) {
    return <Navigate to={getHomePathForRole(user.role)} replace />
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    setLoading(true)
    
    try {
      const ok = await login(username, password)
      if (!ok) {
        setError('Sai tên đăng nhập hoặc mật khẩu.')
        return
      }
      navigate(getHomePathForRole(ok.role), { replace: true })
    } catch (err: any) {
      setError(err.message || 'Đăng nhập thất bại. Vui lòng thử lại.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <CenteredCardLayout>
      <div className="mb-6 sm:mb-8">
        <h1 className="mb-2 text-title-sm font-semibold text-gray-800 sm:text-title-md">
          Đăng nhập hệ thống
        </h1>
      </div>

      <form onSubmit={handleSubmit} className="space-y-5">
        <div>
          <label
            htmlFor="username"
            className="mb-1.5 block text-sm font-medium text-gray-700"
          >
            Tên đăng nhập (Email hoặc Mã SV)
          </label>
          <input
            id="username"
            autoComplete="username"
            className="h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-[#1976d2] focus:outline-hidden focus:ring-1 focus:ring-[#1976d2]"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            placeholder="admin@remedial.edu.vn / SV001"
            required
          />
        </div>
        <div>
          <label
            htmlFor="password"
            className="mb-1.5 block text-sm font-medium text-gray-700"
          >
            Mật khẩu
          </label>
          <input
            id="password"
            type="password"
            autoComplete="current-password"
            className="h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-[#1976d2] focus:outline-hidden focus:ring-1 focus:ring-[#1976d2]"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="Mật khẩu"
            required
          />
        </div>

        {error ? (
          <p className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {error}
          </p>
        ) : null}

        <button
          type="submit"
          disabled={loading}
          className="w-full rounded-lg bg-[#1976d2] py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-[#1565c0] disabled:opacity-70 disabled:cursor-not-allowed"
        >
          {loading ? 'Đang đăng nhập...' : 'Đăng nhập'}
        </button>
      </form>
    </CenteredCardLayout>
  )
}
