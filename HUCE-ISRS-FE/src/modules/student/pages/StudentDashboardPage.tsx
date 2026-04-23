import { Link } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { useDemoData } from '@/shared/context/DemoDataContext'

export function StudentDashboardPage() {
  const { user } = useAuth()
  const { getRegistrationsForStudent, getOpenCohortsForRegistration } =
    useDemoData()

  if (!user) return null

  const myCount = getRegistrationsForStudent(user.id).length
  const openCount = getOpenCohortsForRegistration().length

  return (
    <div className="space-y-4">
      <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm">
        <h1 className="text-lg font-semibold text-gray-800">
          Xin chào, {user.displayName}
        </h1>
        <div className="mt-4 flex flex-wrap gap-3">
          <Link
            to="/student/register"
            className="inline-flex rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600"
          >
            Đăng ký phụ đạo
          </Link>
          <Link
            to="/student/registrations"
            className="inline-flex rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
          >
            Môn đã đăng ký
          </Link>
          <Link
            to="/student/instructors"
            className="inline-flex rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
          >
            Giảng viên phụ đạo
          </Link>
        </div>
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
            Đợt đang mở đăng ký
          </p>
          <p className="mt-1 text-2xl font-semibold text-gray-800">{openCount}</p>
        </div>
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
            Môn bạn đã đăng ký
          </p>
          <p className="mt-1 text-2xl font-semibold text-gray-800">{myCount}</p>
        </div>
      </div>
    </div>
  )
}
