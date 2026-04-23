import { Link } from 'react-router-dom'
import { useConfirm } from '@/shared/context/ConfirmContext'
import { useDemoData } from '@/shared/context/DemoDataContext'
import { useToast } from '@/shared/context/ToastContext'

export function AdminDashboardPage() {
  const { state, resetDemoData } = useDemoData()
  const { confirm } = useConfirm()
  const { success } = useToast()

  async function handleResetDemo() {
    const ok = await confirm({
      title: 'Reset dữ liệu demo',
      message:
        'Reset toàn bộ mock (GĐ 1–4: đợt, môn, đăng ký, user, bộ môn, phân công, email, cài đặt) về seed?',
      confirmLabel: 'Reset',
      variant: 'danger',
    })
    if (!ok) return
    resetDemoData()
    success('Đã reset dữ liệu demo về seed.')
  }

  return (
    <div className="space-y-4">
      <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm">
        <h1 className="text-lg font-semibold text-gray-800">Trang quản trị</h1>
        <div className="mt-4 flex flex-wrap gap-2">
          <Link
            to="/admin/cohorts"
            className="inline-flex rounded bg-brand-500 px-3 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600"
          >
            Đợt phụ đạo
          </Link>
          <Link
            to="/admin/users"
            className="inline-flex rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Người dùng
          </Link>
          <Link
            to="/admin/departments"
            className="inline-flex rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Bộ môn
          </Link>
          <Link
            to="/admin/assignments"
            className="inline-flex rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Phân công GV
          </Link>
          <Link
            to="/admin/email-department"
            className="inline-flex rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Gửi email BM
          </Link>
          <Link
            to="/admin/settings"
            className="inline-flex rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Cài đặt
          </Link>
          <Link
            to="/admin/payments"
            className="inline-flex rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Thanh toán
          </Link>
          <Link
            to="/admin/statistics"
            className="inline-flex rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Thống kê đợt
          </Link>
          <button
            type="button"
            className="rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
            onClick={() => void handleResetDemo()}
          >
            Reset dữ liệu demo
          </button>
        </div>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
            Đợt phụ đạo
          </p>
          <p className="mt-1 text-2xl font-semibold text-gray-800">
            {state.cohorts.length}
          </p>
        </div>
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
            Người dùng (mock)
          </p>
          <p className="mt-1 text-2xl font-semibold text-gray-800">
            {state.systemUsers.length}
          </p>
        </div>
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
            Phân công GV
          </p>
          <p className="mt-1 text-2xl font-semibold text-gray-800">
            {state.lecturerAssignments.length}
          </p>
        </div>
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
            Lượt đăng ký
          </p>
          <p className="mt-1 text-2xl font-semibold text-gray-800">
            {state.registrations.length}
          </p>
        </div>
      </div>
    </div>
  )
}
