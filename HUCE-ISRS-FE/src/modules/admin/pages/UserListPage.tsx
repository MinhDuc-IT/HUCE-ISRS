import { Link } from 'react-router-dom'
import { useConfirm } from '@/shared/context/ConfirmContext'
import { useDemoData } from '@/shared/context/DemoDataContext'
import { useToast } from '@/shared/context/ToastContext'
import type { UserRole } from '@/shared/types/auth'

const roleLabels: Record<UserRole, string> = {
  admin: 'Quản trị',
  department: 'Bộ môn',
  student: 'Sinh viên',
}

export function UserListPage() {
  const { state, removeSystemUser } = useDemoData()
  const { confirm } = useConfirm()
  const { success } = useToast()

  async function handleDelete(id: string, label: string) {
    const ok = await confirm({
      title: 'Xóa người dùng',
      message: `Xóa tài khoản "${label}"? (mock)`,
      confirmLabel: 'Xóa',
      variant: 'danger',
    })
    if (!ok) return
    removeSystemUser(id)
    success('Đã xóa người dùng.')
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
                <th className="px-4 py-3 font-semibold">Tên đăng nhập</th>
                <th className="px-4 py-3 font-semibold">Họ tên</th>
                <th className="px-4 py-3 font-semibold">Email</th>
                <th className="px-4 py-3 font-semibold">Vai trò</th>
                <th className="px-4 py-3 text-right font-semibold">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {state.systemUsers.map((u) => (
                <tr key={u.id} className="hover:bg-gray-50/80">
                  <td className="px-4 py-3 font-mono text-xs text-gray-800">
                    {u.username}
                  </td>
                  <td className="px-4 py-3 text-gray-800">{u.displayName}</td>
                  <td className="px-4 py-3 text-gray-600">{u.email}</td>
                  <td className="px-4 py-3 text-gray-600">
                    {roleLabels[u.role]}
                    {u.departmentId ? (
                      <span className="ml-1 text-xs text-gray-400">
                        ({u.departmentId})
                      </span>
                    ) : null}
                  </td>
                  <td className="px-4 py-3 text-right">
                    <Link
                      to={`/admin/users/${u.id}/edit`}
                      className="mr-2 text-brand-500 hover:text-brand-600 no-underline hover:underline"
                    >
                      Sửa
                    </Link>
                    <button
                      type="button"
                      className="text-red-600 hover:underline"
                      onClick={() => void handleDelete(u.id, u.username)}
                    >
                      Xóa
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
