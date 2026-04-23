import { useState, type FormEvent } from 'react'
import { Link, Navigate, useNavigate, useParams } from 'react-router-dom'
import { useDemoData } from '@/shared/context/DemoDataContext'
import type { UserRole } from '@/shared/types/auth'
import type { MockSystemUser } from '@/shared/types/remedial'

function UserFormFields({ editId }: { editId?: string }) {
  const navigate = useNavigate()
  const { state, addSystemUser, updateSystemUser } = useDemoData()
  const isEdit = Boolean(editId)
  const existing = editId
    ? state.systemUsers.find((u) => u.id === editId)
    : undefined

  const [username, setUsername] = useState(existing?.username ?? '')
  const [displayName, setDisplayName] = useState(existing?.displayName ?? '')
  const [email, setEmail] = useState(existing?.email ?? '')
  const [password, setPassword] = useState('')
  const [role, setRole] = useState<UserRole>(existing?.role ?? 'student')
  const [departmentId, setDepartmentId] = useState(
    existing?.departmentId ?? state.departments[0]?.id ?? '',
  )
  const [error, setError] = useState<string | null>(null)

  function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    if (!username.trim() || !displayName.trim() || !email.trim()) {
      setError('Nhập đủ tên đăng nhập, họ tên và email.')
      return
    }
    const taken = state.systemUsers.some(
      (u) =>
        u.username.trim().toLowerCase() === username.trim().toLowerCase() &&
        u.id !== editId,
    )
    if (taken) {
      setError('Tên đăng nhập đã tồn tại.')
      return
    }
    if (!isEdit && !password.trim()) {
      setError('Nhập mật khẩu cho tài khoản mới (mock).')
      return
    }

    if (isEdit && editId) {
      const patch: Partial<MockSystemUser> = {
        username: username.trim(),
        displayName: displayName.trim(),
        email: email.trim(),
        role,
        departmentId: role === 'department' ? departmentId : undefined,
      }
      if (password.trim()) patch.password = password.trim()
      updateSystemUser(editId, patch)
    } else {
      addSystemUser({
        username: username.trim(),
        displayName: displayName.trim(),
        email: email.trim(),
        password: password.trim(),
        role,
        departmentId: role === 'department' ? departmentId : undefined,
      })
    }
    navigate('/admin/users')
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
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Tên đăng nhập
          </label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            autoComplete="off"
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Họ tên hiển thị
          </label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={displayName}
            onChange={(e) => setDisplayName(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Email
          </label>
          <input
            type="email"
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Mật khẩu {isEdit ? '(để trống nếu giữ nguyên)' : ''}
          </label>
          <input
            type="password"
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            autoComplete="new-password"
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Vai trò
          </label>
          <select
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={role}
            onChange={(e) => setRole(e.target.value as UserRole)}
          >
            <option value="admin">Quản trị</option>
            <option value="department">Bộ môn</option>
            <option value="student">Sinh viên</option>
          </select>
        </div>
        {role === 'department' ? (
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">
              Bộ môn quản lý
            </label>
            <select
              className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
              value={departmentId}
              onChange={(e) => setDepartmentId(e.target.value)}
            >
              {state.departments.map((d) => (
                <option key={d.id} value={d.id}>
                  {d.name}
                </option>
              ))}
            </select>
          </div>
        ) : null}

        {error ? (
          <p className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {error}
          </p>
        ) : null}

        <div className="flex gap-2 pt-2">
          <button
            type="submit"
            className="rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600"
          >
            Lưu
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

export function UserFormPage() {
  const { id } = useParams<{ id?: string }>()
  const { state } = useDemoData()
  if (id && !state.systemUsers.some((u) => u.id === id)) {
    return <Navigate to="/admin/users" replace />
  }
  return <UserFormFields key={id ?? 'new'} editId={id} />
}
