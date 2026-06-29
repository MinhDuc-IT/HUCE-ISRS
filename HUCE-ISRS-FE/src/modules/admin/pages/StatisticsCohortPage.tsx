import { useState, useEffect } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiTermStatisticsSummary } from '@/shared/types/api'

function formatVnd(n: number) {
  return new Intl.NumberFormat('vi-VN').format(n) + ' đ'
}

export function StatisticsCohortPage() {
  const [rows, setRows] = useState<ApiTermStatisticsSummary[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  async function fetchSummaries() {
    try {
      setLoading(true)
      setError(null)
      const res = await apiFetch<{ data: ApiTermStatisticsSummary[] }>(
        '/admin/statistics/terms/summaries',
      )
      setRows(res.data || [])
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Lỗi khi tải thống kê: ' + msg)
      setRows([])
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    void fetchSummaries()
  }, [])

  return (
    <div className="space-y-4">
      {error && (
        <div className="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      <h1 className="text-lg font-semibold text-gray-800">Thống kê đợt phụ đạo</h1>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 text-center font-semibold w-16">TT</th>
                <th className="px-4 py-3 font-semibold">Tên đợt phụ đạo</th>
                <th className="px-4 py-3 text-right font-semibold">Số sinh viên</th>
                <th className="px-4 py-3 text-right font-semibold">Số môn học</th>
                <th className="px-4 py-3 text-right font-semibold">Tổng tiền</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan={5} className="px-4 py-8 text-center text-gray-500">
                    Đang tải dữ liệu...
                  </td>
                </tr>
              ) : rows.length === 0 ? (
                <tr>
                  <td colSpan={5} className="px-4 py-8 text-center text-gray-500">
                    Chưa có đợt phụ đạo nào trong hệ thống.
                  </td>
                </tr>
              ) : (
                rows.map((row, index) => (
                  <tr key={row.remedial_term_id} className="hover:bg-gray-50/80">
                    <td className="px-4 py-3 text-center text-gray-600">{index + 1}</td>
                    <td className="px-4 py-3 font-medium text-gray-800">
                      {row.remedial_term_name}
                    </td>
                    <td className="px-4 py-3 text-right tabular-nums">
                      {row.distinct_student_count}
                    </td>
                    <td className="px-4 py-3 text-right tabular-nums">
                      {row.courses_with_registration_count}
                    </td>
                    <td className="px-4 py-3 text-right font-medium text-emerald-800 tabular-nums">
                      {formatVnd(row.total_revenue)}
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
