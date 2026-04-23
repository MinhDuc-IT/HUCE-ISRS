import { useMemo, useState } from 'react'
import { useDemoData } from '@/shared/context/DemoDataContext'

function formatVnd(n: number) {
  return new Intl.NumberFormat('vi-VN').format(n) + ' đ'
}

export function StatisticsCohortPage() {
  const { state, getCohortStatistics } = useDemoData()
  const [cohortId, setCohortId] = useState('')

  const selectedCohortId = useMemo(() => {
    if (state.cohorts.length === 0) return ''
    if (state.cohorts.some((c) => c.id === cohortId)) return cohortId
    return state.cohorts[0].id
  }, [cohortId, state.cohorts])

  const stats = useMemo(() => {
    if (!selectedCohortId) return null
    return getCohortStatistics(selectedCohortId)
  }, [selectedCohortId, getCohortStatistics])

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-lg font-semibold text-gray-800">Thống kê đợt phụ đạo</h1>
      </div>

      <div className="max-w-md">
        <label className="mb-1 block text-sm font-medium text-gray-700">
          Chọn đợt
        </label>
        <select
          className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
          value={selectedCohortId}
          onChange={(e) => setCohortId(e.target.value)}
        >
          {state.cohorts.map((c) => (
            <option key={c.id} value={c.id}>
              {c.name}
            </option>
          ))}
        </select>
      </div>

      {stats ? (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
              Tổng số sinh viên
            </p>
            <p className="mt-1 text-2xl font-semibold text-gray-800">
              {stats.distinctStudentCount}
            </p>
            <p className="mt-1 text-xs text-gray-400">Có ít nhất 1 đăng ký</p>
          </div>
          <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
              Tổng số môn (danh mục)
            </p>
            <p className="mt-1 text-2xl font-semibold text-gray-800">
              {stats.catalogCourseCount}
            </p>
            <p className="mt-1 text-xs text-gray-400">Môn thuộc đợt</p>
          </div>
          <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
              Môn có SV đăng ký
            </p>
            <p className="mt-1 text-2xl font-semibold text-gray-800">
              {stats.coursesWithRegistrationCount}
            </p>
            <p className="mt-1 text-xs text-gray-400">Số môn có lượt đăng ký</p>
          </div>
          <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
              Lớp đã phân công GV
            </p>
            <p className="mt-1 text-2xl font-semibold text-gray-800">
              {stats.assignedClassCount}
            </p>
            <p className="mt-1 text-xs text-gray-400">Số dòng phân công có tên GV</p>
          </div>
          <div className="rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 shadow-theme-sm sm:col-span-2 lg:col-span-4">
            <p className="text-xs font-medium uppercase tracking-wide text-emerald-900">
              Tổng tiền phụ đạo (ước tính)
            </p>
            <p className="mt-1 text-2xl font-semibold text-emerald-950">
              {formatVnd(stats.totalRevenue)}
            </p>
            <p className="mt-1 text-xs text-emerald-800">
              = số lượt đăng ký × (đơn giá + VAT theo cài đặt)
            </p>
          </div>
        </div>
      ) : (
        <p className="text-sm text-gray-500">Chưa có đợt phụ đạo.</p>
      )}
    </div>
  )
}
