import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/shared/utils/apiClient'
import { useToast } from '@/shared/context/ToastContext'
interface SystemUser {
  id: number
  name: string
  email: string
  role: string
  student_code?: string
  department_id?: number
}

const roleLabels: Record<string, string> = {
  admin: 'Quản trị',
  bo_mon: 'Bộ môn',
  sinh_vien: 'Sinh viên',
  department: 'Bộ môn',
  student: 'Sinh viên',
}

export function UserListPage() {
  const [users, setUsers] = useState<SystemUser[]>([])
  const [loading, setLoading] = useState(true)
  const { success, error: showError } = useToast()

  async function fetchUsers() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: SystemUser[] }>('/admin/users')
      setUsers(res.data || [])
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : String(err)
      showError('Lỗi tải danh sách người dùng: ' + msg)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchUsers()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])


  async function handleDelete(id: number, label: string) {
    if (!window.confirm(`Xóa tài khoản "${label}"? Thao tác không thể hoàn tác.`)) return
    try {
      await apiFetch(`/admin/users/${id}`, { method: 'DELETE' })
      success('Đã xóa người dùng.')
      fetchUsers()
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : String(err)
      showError(msg || 'Không thể xóa người dùng.')
    }
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-lg font-semibold text-gray-800">Người dùng</h1>
        </div>
        <Link
          to="/admin/users/new"
          className="inline-flex rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600"
        >
          + Thêm người dùng
        </Link>
      </div>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 font-semibold">#</th>
                <th className="px-4 py-3 font-semibold">Họ tên</th>
                <th className="px-4 py-3 font-semibold">Email</th>
                <th className="px-4 py-3 font-semibold">Mã SV</th>
                <th className="px-4 py-3 font-semibold">Vai trò</th>
                <th className="px-4 py-3 text-right font-semibold">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan={6} className="px-4 py-8 text-center text-gray-500">Đang tải...</td>
                </tr>
              ) : users.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-4 py-8 text-center text-gray-500">Chưa có người dùng nào.</td>
                </tr>
              ) : (
                users.map((u, i) => (
                  <tr key={u.id} className="hover:bg-gray-50/80">
                    <td className="px-4 py-3 text-gray-500">{i + 1}</td>
                    <td className="px-4 py-3 font-medium text-gray-800">{u.name}</td>
                    <td className="px-4 py-3 text-gray-600">{u.email || '-'}</td>
                    <td className="px-4 py-3 font-mono text-xs text-gray-600">{u.student_code || '-'}</td>
                    <td className="px-4 py-3 text-gray-600">
                      {roleLabels[u.role] ?? u.role}
                    </td>
                    <td className="px-4 py-3 text-right">
                      <Link
                        to={`/admin/users/${u.id}/edit`}
                        className="mr-3 text-brand-500 hover:text-brand-600 no-underline hover:underline"
                      >
                        Sửa
                      </Link>
                      <button
                        type="button"
                        className="text-red-600 hover:underline"
                        onClick={() => void handleDelete(u.id, u.name)}
                      >
                        Xóa
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
