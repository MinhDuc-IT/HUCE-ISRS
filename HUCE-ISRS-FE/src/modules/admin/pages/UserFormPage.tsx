import { useState, useEffect, type FormEvent } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { apiFetch } from '@/shared/utils/apiClient'

import type { ApiDepartment } from '@/shared/types/api'

export function UserFormPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id?: string }>()
  const isEdit = Boolean(id)

  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [role, setRole] = useState('admin')
  const [departmentId, setDepartmentId] = useState('')
  const [departments, setDepartments] = useState<ApiDepartment[]>([])
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    fetchDepartments()
    if (isEdit && id) fetchUser(id)
  }, [id])

  async function fetchDepartments() {
    try {
      const res = await apiFetch<{ data: ApiDepartment[] }>('/admin/departments')
      setDepartments(res.data || [])
    } catch {
      // silent
    }
  }

  async function fetchUser(userId: string) {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: any }>(`/admin/users/${userId}`)
      const u = res.data
      setName(u.name ?? '')
      setEmail(u.email ?? '')
      setRole(u.role ?? 'admin')
      setDepartmentId(u.department_id?.toString() ?? '')
    } catch (err: any) {
      setError('Không tải được thông tin người dùng.')
    } finally {
      setLoading(false)
    }
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)

    if (!name.trim() || !email.trim()) {
      setError('Nhập đủ họ tên và email.')
      return
    }
    if (!isEdit && !password.trim()) {
      setError('Nhập mật khẩu cho tài khoản mới.')
      return
    }
    if (!isEdit && password.trim().length < 6) {
      setError('Mật khẩu phải có ít nhất 6 ký tự.')
      return
    }

    const payload: any = {
      name: name.trim(),
      email: email.trim(),
      role,
      department_id: role === 'bo_mon' ? (departmentId || null) : null,
    }
    if (password.trim()) payload.password = password.trim()

    try {
      setSubmitting(true)
      if (isEdit && id) {
        await apiFetch(`/admin/users/${id}`, { method: 'PATCH', data: payload })
      } else {
        await apiFetch('/admin/users', { method: 'POST', data: payload })
      }
      navigate('/admin/users')
    } catch (err: any) {
      setError(err.message || 'Lưu thất bại. Vui lòng thử lại.')
    } finally {
      setSubmitting(false)
    }
  }

  const inputClass = "w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:ring-2 focus:ring-brand-500/10"

  if (loading) {
    return <p className="text-center text-gray-500 py-10">Đang tải...</p>
  }

  return (
    <div className="mx-auto max-w-xl space-y-4">
      <div>
        <Link
          to="/admin/users"
          className="text-sm text-brand-500 hover:text-brand-600 no-underline hover:underline"
        >
          ← Danh sách người dùng
        </Link>
        <h1 className="mt-2 text-lg font-semibold text-gray-800">
          {isEdit ? 'Sửa người dùng' : 'Thêm người dùng'}
        </h1>
      </div>

      <form
        onSubmit={handleSubmit}
        className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm"
      >
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Họ tên</label>
          <input className={inputClass} value={name} onChange={(e) => setName(e.target.value)} />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Email</label>
          <input type="email" className={inputClass} value={email} onChange={(e) => setEmail(e.target.value)} />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Mật khẩu {isEdit ? '(để trống nếu giữ nguyên)' : ''}
          </label>
          <input
            type="password"
            className={inputClass}
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            autoComplete="new-password"
            minLength={isEdit ? undefined : 6}
          />
          {!isEdit && (
            <p className="mt-1 text-xs text-gray-500">Tối thiểu 6 ký tự.</p>
          )}
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Vai trò</label>
          <select className={inputClass} value={role} onChange={(e) => setRole(e.target.value)}>
            <option value="admin">Quản trị</option>
            <option value="bo_mon">Bộ môn</option>
            <option value="sinh_vien">Sinh viên</option>
          </select>
        </div>

        {role === 'bo_mon' && (
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Bộ môn quản lý</label>
            <select className={inputClass} value={departmentId} onChange={(e) => setDepartmentId(e.target.value)}>
              <option value="">-- Chọn bộ môn --</option>
              {departments.map((d) => (
                <option key={d.id} value={d.id}>
                  {d.department_code} – {d.department_name}
                </option>
              ))}
            </select>
          </div>
        )}

        {error && (
          <p className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {error}
          </p>
        )}

        <div className="flex gap-2 pt-2">
          <button
            type="submit"
            disabled={submitting}
            className="rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-70"
          >
            {submitting ? 'Đang lưu...' : 'Lưu'}
          </button>
          <Link
            to="/admin/users"
            className="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Hủy
          </Link>
        </div>
      </form>
    </div>
  )
}
