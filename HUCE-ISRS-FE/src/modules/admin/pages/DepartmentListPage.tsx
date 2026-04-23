import { Link } from 'react-router-dom'
import { useDemoData } from '@/shared/context/DemoDataContext'

export function DepartmentListPage() {
  const { state } = useDemoData()

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-lg font-semibold text-gray-800">Bộ môn</h1>
      </div>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 font-semibold">Mã</th>
                <th className="px-4 py-3 font-semibold">Tên bộ môn</th>
                <th className="px-4 py-3 font-semibold">Email trưởng BM</th>
                <th className="px-4 py-3 font-semibold">SĐT trưởng BM</th>
                <th className="px-4 py-3 text-right font-semibold">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {state.departments.map((d) => (
                <tr key={d.id} className="hover:bg-gray-50/80">
                  <td className="px-4 py-3 font-mono text-xs text-gray-700">
                    {d.code}
                  </td>
                  <td className="px-4 py-3 font-medium text-gray-800">{d.name}</td>
                  <td className="px-4 py-3 text-gray-600">{d.headEmail}</td>
                  <td className="px-4 py-3 text-gray-600">{d.headPhone}</td>
                  <td className="px-4 py-3 text-right">
                    <Link
                      to={`/admin/departments/${d.id}/edit`}
                      className="text-brand-500 hover:text-brand-600 no-underline hover:underline"
                    >
                      Sửa liên hệ
                    </Link>
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
