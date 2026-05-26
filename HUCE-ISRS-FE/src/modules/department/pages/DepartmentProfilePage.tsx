import { useEffect, useState, type FormEvent } from 'react'
import { Link, Navigate } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiDepartment } from '@/shared/types/api'

export function DepartmentProfilePage() {
  const { user } = useAuth()
  const [profile, setProfile] = useState<ApiDepartment | null>(null)
  const [email, setEmail] = useState('')
  const [phone, setPhone] = useState('')
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  useEffect(() => {
    if (!user?.departmentId) return
    loadProfile()
  }, [user?.departmentId])

  async function loadProfile() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiDepartment }>('/department/me')
      const d = res.data
      setProfile(d)
      setEmail(d.email ?? '')
      setPhone(d.phone_number ?? '')
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Không tải được thông tin bộ môn: ' + msg)
    } finally {
      setLoading(false)
    }
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    setMessage(null)

    if (!email.trim()) {
      setError('Nhập email bộ môn.')
      return
    }

    try {
      setSaving(true)
      const res = await apiFetch<{ data: ApiDepartment }>('/department/me', {
        method: 'PATCH',
        data: {
          email: email.trim() || null,
          phone_number: phone.trim() || null,
        },
      })
      setProfile(res.data)
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
        className="mx-auto max-w-xl space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm"
      >
        <div className="rounded border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600">
          <span className="font-medium text-gray-700">Mã:</span> {profile.department_code}
          <span className="mx-2">·</span>
          <span className="font-medium text-gray-700">Tên:</span> {profile.department_name}
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Email bộ môn</label>
          <input
            type="email"
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Số điện thoại</label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            value={phone}
            onChange={(e) => setPhone(e.target.value)}
          />
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
