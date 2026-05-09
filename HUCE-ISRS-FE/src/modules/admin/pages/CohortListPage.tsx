import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/shared/utils/apiClient'
import { useToast } from '@/shared/context/ToastContext'

interface TutoringTerm {
  id: number
  Name: string
  StartDate: string
  EndDate: string
  RegistrationStartDate: string
  RegistrationEndDate: string
}

export function CohortListPage() {
  const [terms, setTerms] = useState<TutoringTerm[]>([])
  const [loading, setLoading] = useState(true)
  const { success, error: showError } = useToast()

  useEffect(() => {
    fetchTerms()
  }, [])

  async function fetchTerms() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: TutoringTerm[] }>('/admin/tutoring-terms')
      setTerms(res.data || [])
    } catch (err: any) {
      showError('Lỗi tải danh sách đợt phụ đạo: ' + err.message)
    } finally {
      setLoading(false)
    }
  }

  async function handleDelete(id: number, name: string) {
    if (!window.confirm(`Xóa đợt "${name}"? Thao tác không thể hoàn tác.`)) return
    try {
      await apiFetch(`/admin/tutoring-terms/${id}`, { method: 'DELETE' })
      success('Đã xóa đợt phụ đạo.')
      fetchTerms()
    } catch (err: any) {
      showError(err.message || 'Không thể xóa đợt phụ đạo.')
    }
  }

  function formatDate(d: string) {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('vi-VN')
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-lg font-semibold text-gray-800">Đợt phụ đạo</h1>
        </div>
        <Link
          to="/admin/cohorts/new"
          className="inline-flex items-center rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600"
        >
          + Thêm đợt
        </Link>
      </div>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 font-semibold">#</th>
                <th className="px-4 py-3 font-semibold">Tên đợt</th>
                <th className="px-4 py-3 font-semibold">Ngày bắt đầu</th>
                <th className="px-4 py-3 font-semibold">Ngày kết thúc</th>
                <th className="px-4 py-3 font-semibold">Đăng ký từ</th>
                <th className="px-4 py-3 font-semibold">Đăng ký đến</th>
                <th className="px-4 py-3 font-semibold text-right">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan={7} className="px-4 py-8 text-center text-gray-500">
                    Đang tải...
                  </td>
                </tr>
              ) : terms.length === 0 ? (
                <tr>
                  <td colSpan={7} className="px-4 py-8 text-center text-gray-500">
                    Chưa có đợt phụ đạo nào.
                  </td>
                </tr>
              ) : (
                terms.map((c, i) => (
                  <tr key={c.id} className="hover:bg-gray-50/80">
                    <td className="px-4 py-3 text-gray-500">{i + 1}</td>
                    <td className="px-4 py-3 font-medium text-gray-800">{c.Name}</td>
                    <td className="px-4 py-3 text-gray-600">{formatDate(c.StartDate)}</td>
                    <td className="px-4 py-3 text-gray-600">{formatDate(c.EndDate)}</td>
                    <td className="px-4 py-3 text-gray-600">{formatDate(c.RegistrationStartDate)}</td>
                    <td className="px-4 py-3 text-gray-600">{formatDate(c.RegistrationEndDate)}</td>
                    <td className="px-4 py-3 text-right">
                      <Link
                        to={`/admin/cohorts/${c.id}/edit`}
                        className="mr-3 text-brand-500 hover:text-brand-600 no-underline hover:underline"
                      >
                        Sửa
                      </Link>
                      <button
                        type="button"
                        className="text-red-600 hover:underline"
                        onClick={() => void handleDelete(c.id, c.Name)}
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
