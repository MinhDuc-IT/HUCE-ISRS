import { useEffect, useState, type FormEvent } from 'react'
import { Link, Navigate } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiDepartment } from '@/shared/types/api'

export function DepartmentProfilePage() {
  const { user } = useAuth()
  const [profile, setProfile] = useState<ApiDepartment | null>(null)
  const [departmentName, setDepartmentName] = useState('')
  const [contactEmail, setContactEmail] = useState('')
  const [phone, setPhone] = useState('')
  const [loginName, setLoginName] = useState('')
  const [loginEmail, setLoginEmail] = useState('')
  const [loginPassword, setLoginPassword] = useState('')
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  async function loadProfile() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiDepartment }>('/department/me')
      const d = res.data
      setProfile(d)
      setDepartmentName(d.department_name ?? '')
      setContactEmail(d.email ?? '')
      setPhone(d.phone_number ?? '')
      setLoginName(d.login_user?.name ?? '')
      setLoginEmail(d.login_user?.email ?? '')
      setLoginPassword('')
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Không tải được thông tin bộ môn: ' + msg)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    if (!user?.departmentId) return
    void loadProfile()
  }, [user?.departmentId])

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    setMessage(null)

    if (!departmentName.trim()) {
      setError('Nhập tên bộ môn.')
      return
    }
    if (!loginName.trim() || !loginEmail.trim()) {
      setError('Nhập đủ thông tin tài khoản đăng nhập.')
      return
    }

    try {
      setSaving(true)
      const res = await apiFetch<{ data: ApiDepartment }>('/department/me', {
        method: 'PATCH',
        data: {
          name: departmentName.trim(),
          email: contactEmail.trim() || null,
          phone_number: phone.trim() || null,
          login_user: {
            name: loginName.trim(),
            email: loginEmail.trim(),
            ...(loginPassword.trim() ? { password: loginPassword.trim() } : {}),
          },
        },
      })
      const d = res.data
      setProfile(d)
      setDepartmentName(d.department_name ?? '')
      setContactEmail(d.email ?? '')
      setPhone(d.phone_number ?? '')
      setLoginName(d.login_user?.name ?? '')
      setLoginEmail(d.login_user?.email ?? '')
      setLoginPassword('')
      setMessage('Đã lưu thông tin bộ môn.')
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Lưu thất bại: ' + msg)
    } finally {
      setSaving(false)
    }
  }

  if (!user?.departmentId) {
    return (
      <p className="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Tài khoản chưa gắn bộ môn. Hãy đăng xuất và đăng nhập lại bằng tài khoản bộ môn.
      </p>
    )
  }

  if (loading) {
    return <p className="text-sm text-gray-500">Đang tải...</p>
  }

  if (!profile) {
    return <Navigate to="/department" replace />
  }

  return (
    <div className="space-y-4">
      <h1 className="text-lg font-semibold text-gray-800">Thông tin bộ môn của tôi</h1>

      {error && (
        <div className="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}
      {message && (
        <div className="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {message}
        </div>
      )}

      <form
        onSubmit={handleSubmit}
        autoComplete="off"
        className="mx-auto max-w-xl space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm"
      >
        <div className="hidden" aria-hidden="true">
          <input type="text" tabIndex={-1} autoComplete="username" />
          <input type="password" tabIndex={-1} autoComplete="current-password" />
        </div>
        <div className="rounded border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600">
          <span className="font-medium text-gray-700">Mã bộ môn:</span> {profile.department_code}
        </div>

        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Tên bộ môn</label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            value={departmentName}
            onChange={(e) => setDepartmentName(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Email liên hệ bộ môn</label>
          <input
            type="email"
            name="department-contact-email"
            autoComplete="off"
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            value={contactEmail}
            onChange={(e) => setContactEmail(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Số điện thoại liên hệ</label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            value={phone}
            onChange={(e) => setPhone(e.target.value)}
          />
        </div>

        <div className="space-y-4 border-t border-gray-200 pt-4">
          <p className="text-sm font-semibold text-gray-700">Tài khoản đăng nhập</p>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Họ tên hiển thị</label>
            <input
              className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
              value={loginName}
              onChange={(e) => setLoginName(e.target.value)}
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Email đăng nhập</label>
            <input
              type="email"
              name="department-login-email"
              autoComplete="off"
              className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
              value={loginEmail}
              onChange={(e) => setLoginEmail(e.target.value)}
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">
              Mật khẩu (để trống nếu giữ nguyên)
            </label>
            <input
              type="password"
              name="department-login-password"
              autoComplete="new-password"
              className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
              value={loginPassword}
              onChange={(e) => setLoginPassword(e.target.value)}
            />
          </div>
        </div>

        <div className="flex gap-2 pt-2">
          <button
            type="submit"
            disabled={saving}
            className="rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-70"
          >
            {saving ? 'Đang lưu...' : 'Lưu'}
          </button>
          <Link
            to="/department"
            className="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Hủy
          </Link>
        </div>
      </form>
    </div>
  )
}
