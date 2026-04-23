import { Link } from 'react-router-dom'

export function DepartmentDashboardPage() {
  return (
    <div className="space-y-4">
      <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm">
        <h1 className="text-lg font-semibold text-gray-800">Trang bộ môn</h1>
        <div className="mt-4 flex flex-wrap gap-3">
          <Link
            to="/department/profile"
            className="inline-flex rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600"
          >
            Thông tin bộ môn
          </Link>
          <Link
            to="/department/assignments"
            className="inline-flex rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
          >
            Phân công giảng viên
          </Link>
        </div>
      </div>
    </div>
  )
}
