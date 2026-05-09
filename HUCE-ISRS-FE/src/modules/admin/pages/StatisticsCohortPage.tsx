import { useState, useEffect } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'

function formatVnd(n: number) {
  return new Intl.NumberFormat('vi-VN').format(n) + ' đ'
}

interface TermOption {
  id: string
  name: string
}

interface TermStatistics {
  cohortId: string
  distinctStudentCount: number
  catalogCourseCount: number
  coursesWithRegistrationCount: number
  assignedClassCount: number
  totalRevenue: number
}

export function StatisticsCohortPage() {
  const [terms, setTerms] = useState<TermOption[]>([])
  const [selectedCohortId, setSelectedCohortId] = useState<string>('')
  const [stats, setStats] = useState<TermStatistics | null>(null)
  const [loadingTerms, setLoadingTerms] = useState(true)
  const [loadingStats, setLoadingStats] = useState(false)
  const [error, setError] = useState<string | null>(null)

  // Load terms when mounted
  useEffect(() => {
    fetchTerms()
  }, [])

  // Load stats when selectedCohortId changes
  useEffect(() => {
    if (selectedCohortId) {
      fetchStats(selectedCohortId)
    } else {
      setStats(null)
    }
  }, [selectedCohortId])

  async function fetchTerms() {
    try {
      setLoadingTerms(true)
      const res = await apiFetch<{ data: { terms: TermOption[] } }>('/admin/statistics/terms')
      const fetchedTerms = res.data?.terms || []
      setTerms(fetchedTerms)
      if (fetchedTerms.length > 0) {
        setSelectedCohortId(fetchedTerms[0].id.toString())
      }
    } catch (err: any) {
      setError('Lỗi khi tải danh sách đợt phụ đạo: ' + err.message)
    } finally {
      setLoadingTerms(false)
    }
  }

  async function fetchStats(termId: string) {
    try {
      setLoadingStats(true)
      const res = await apiFetch<{ data: TermStatistics }>(`/admin/statistics/terms/${termId}`)
      setStats(res.data)
    } catch (err: any) {
      setError('Lỗi khi tải thống kê: ' + err.message)
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

      <div>
        <h1 className="text-lg font-semibold text-gray-800">Thống kê đợt phụ đạo</h1>
      </div>

      <div className="max-w-md relative">
        {loadingTerms && (
          <div className="absolute inset-0 bg-white/60 z-10 flex items-center backdrop-blur-[1px]">
            <span className="text-gray-500 font-medium text-sm ml-2">Đang tải...</span>
          </div>
        )}
        <label className="mb-1 block text-sm font-medium text-gray-700">
          Chọn đợt
        </label>
        <select
          className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
          value={selectedCohortId}
          onChange={(e) => setSelectedCohortId(e.target.value)}
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
          <div className="absolute inset-0 bg-white/60 z-10 flex items-center justify-center backdrop-blur-[1px]">
            <span className="text-[#1976d2] font-medium">Đang tính toán thống kê...</span>
          </div>
        )}

        {stats ? (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
              <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                Tổng số sinh viên
              </p>
              <p className="mt-1 text-2xl font-semibold text-gray-800">
                {stats.distinctStudentCount}
              </p>
              <p className="mt-1 text-xs text-gray-400">Có đăng ký (active)</p>
            </div>
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
              <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                Tổng số môn (danh mục)
              </p>
              <p className="mt-1 text-2xl font-semibold text-gray-800">
                {stats.catalogCourseCount}
              </p>
              <p className="mt-1 text-xs text-gray-400">Môn thuộc đợt</p>
            </div>
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
              <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                Môn có SV đăng ký
              </p>
              <p className="mt-1 text-2xl font-semibold text-gray-800">
                {stats.coursesWithRegistrationCount}
              </p>
              <p className="mt-1 text-xs text-gray-400">Số môn có lượt đăng ký</p>
            </div>
            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
              <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                Lớp đã phân công GV
              </p>
              <p className="mt-1 text-2xl font-semibold text-gray-800">
                {stats.assignedClassCount}
              </p>
              <p className="mt-1 text-xs text-gray-400">Lớp có tên GV</p>
            </div>
            <div className="rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 shadow-sm sm:col-span-2 lg:col-span-4">
              <p className="text-xs font-medium uppercase tracking-wide text-emerald-900">
                Tổng tiền phụ đạo (ước tính)
              </p>
              <p className="mt-1 text-2xl font-semibold text-emerald-950">
                {formatVnd(stats.totalRevenue)}
              </p>
              <p className="mt-1 text-xs text-emerald-800">
                = Tổng số lượt đăng ký × (Đơn giá + VAT)
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
