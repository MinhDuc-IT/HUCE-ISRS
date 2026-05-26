import { useState, useEffect } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiTermStatistics } from '@/shared/types/api'

function formatVnd(n: number) {
  return new Intl.NumberFormat('vi-VN').format(n) + ' đ'
}

interface TermOption {
  id: number
  name: string
}

export function StatisticsCohortPage() {
  const [terms, setTerms] = useState<TermOption[]>([])
  const [selectedTermId, setSelectedTermId] = useState('')
  const [stats, setStats] = useState<ApiTermStatistics | null>(null)
  const [loadingTerms, setLoadingTerms] = useState(true)
  const [loadingStats, setLoadingStats] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    fetchTerms()
  }, [])

  useEffect(() => {
    if (selectedTermId) {
      fetchStats(selectedTermId)
    } else {
      setStats(null)
    }
  }, [selectedTermId])

  async function fetchTerms() {
    try {
      setLoadingTerms(true)
      const res = await apiFetch<{ data: { terms: TermOption[] } }>('/admin/statistics/terms')
      const fetchedTerms = res.data?.terms || []
      setTerms(fetchedTerms)
      if (fetchedTerms.length > 0) {
        setSelectedTermId(String(fetchedTerms[0].id))
      }
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Lỗi khi tải danh sách đợt phụ đạo: ' + msg)
    } finally {
      setLoadingTerms(false)
    }
  }

  async function fetchStats(termId: string) {
    try {
      setLoadingStats(true)
      const res = await apiFetch<{ data: ApiTermStatistics }>(
        `/admin/statistics/terms/${termId}`,
      )
      setStats(res.data)
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Lỗi khi tải thống kê: ' + msg)
    } finally {
      setLoadingStats(false)
    }
  }

  return (
    <div className="space-y-4">
      {error && (
        <div className="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      <h1 className="text-lg font-semibold text-gray-800">Thống kê đợt phụ đạo</h1>

      <div className="max-w-md relative">
        <label className="mb-1 block text-sm font-medium text-gray-700">Chọn đợt</label>
        <select
          className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
          value={selectedTermId}
          onChange={(e) => setSelectedTermId(e.target.value)}
          disabled={loadingTerms || terms.length === 0}
        >
          {terms.map((c) => (
            <option key={c.id} value={c.id}>
              {c.name}
            </option>
          ))}
        </select>
      </div>

      <div className="relative min-h-[200px]">
        {loadingStats && (
          <p className="text-sm text-gray-500">Đang tính toán thống kê...</p>
        )}

        {stats && !loadingStats ? (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
              <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                Tổng số sinh viên
              </p>
              <p className="mt-1 text-2xl font-semibold text-gray-800">
                {stats.distinct_student_count}
              </p>
            </div>
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
              <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                Tổng số môn (danh mục)
              </p>
              <p className="mt-1 text-2xl font-semibold text-gray-800">
                {stats.catalog_course_count}
              </p>
            </div>
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
              <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                Môn có SV đăng ký
              </p>
              <p className="mt-1 text-2xl font-semibold text-gray-800">
                {stats.courses_with_registration_count}
              </p>
            </div>
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
              <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                Đã phân công GV
              </p>
              <p className="mt-1 text-2xl font-semibold text-gray-800">
                {stats.assigned_class_count}
              </p>
            </div>
            <div className="rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 shadow-sm sm:col-span-2 lg:col-span-4">
              <p className="text-xs font-medium uppercase tracking-wide text-emerald-900">
                Tổng tiền phụ đạo (ước tính)
              </p>
              <p className="mt-1 text-2xl font-semibold text-emerald-950">
                {formatVnd(stats.total_revenue)}
              </p>
              <p className="mt-1 text-xs text-emerald-800">
                Tổng {stats.total_registrations} lượt đăng ký
              </p>
            </div>
          </div>
        ) : !loadingTerms && terms.length === 0 ? (
          <p className="text-sm text-gray-500 mt-4">Chưa có đợt phụ đạo nào trong hệ thống.</p>
        ) : null}
      </div>
    </div>
  )
}
