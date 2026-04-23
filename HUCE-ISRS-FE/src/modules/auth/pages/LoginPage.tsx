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

  if (user) {
    return <Navigate to={getHomePathForRole(user.role)} replace />
  }

  function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    const ok = login(username, password)
    if (!ok) {
      setError('Sai tên đăng nhập hoặc mật khẩu (demo).')
      return
    }
    navigate(getHomePathForRole(ok.role), { replace: true })
  }

  return (
    <CenteredCardLayout>
      <div className="mb-6 sm:mb-8">
        <h1 className="mb-2 text-title-sm font-semibold text-gray-800 dark:text-white/90 sm:text-title-md">
          Đăng nhập HUCE-ISRS
        </h1>
      </div>

      <form onSubmit={handleSubmit} className="space-y-5">
        <div>
          <label
            htmlFor="username"
            className="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
          >
            Tên đăng nhập
          </label>
          <input
            id="username"
            autoComplete="username"
            className="h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            placeholder="admin / bomon / sinhvien"
          />
        </div>
        <div>
          <label
            htmlFor="password"
            className="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
          >
            Mật khẩu
          </label>
          <input
            id="password"
            type="password"
            autoComplete="current-password"
            className="h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="trùng với tên đăng nhập (demo)"
          />
        </div>

        {error ? (
          <p className="rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-sm text-error-700 dark:border-error-900 dark:bg-error-950/40 dark:text-error-200">
            {error}
          </p>
        ) : null}

        <button
          type="submit"
          className="w-full rounded-lg bg-brand-500 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600"
        >
          Đăng nhập
        </button>
      </form>

      <div className="mt-6 rounded border border-gray-100 bg-gray-50 p-3 text-xs text-gray-600">
        <p className="mb-1 font-medium text-gray-700">Tài khoản thử:</p>
        <ul className="list-inside list-disc space-y-0.5">
          <li>
            <code className="rounded bg-white px-1">admin</code> /{' '}
            <code className="rounded bg-white px-1">admin</code>
          </li>
          <li>
            <code className="rounded bg-white px-1">bomon</code> /{' '}
            <code className="rounded bg-white px-1">bomon</code>
          </li>
          <li>
            <code className="rounded bg-white px-1">sinhvien</code> /{' '}
            <code className="rounded bg-white px-1">sinhvien</code>
          </li>
        </ul>
      </div>
    </CenteredCardLayout>
  )
}
