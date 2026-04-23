import { useState, type FormEvent } from 'react'
import { Link, Navigate, useNavigate, useParams } from 'react-router-dom'
import { useDemoData } from '@/shared/context/DemoDataContext'
import type { CohortStatus } from '@/shared/types/remedial'

function CohortFormFields({ editId }: { editId?: string }) {
  const navigate = useNavigate()
  const { getCohort, addCohort, updateCohort } = useDemoData()
  const isEdit = Boolean(editId)
  const cohort = editId ? getCohort(editId) : undefined

  const [name, setName] = useState(cohort?.name ?? '')
  const [startDate, setStartDate] = useState(cohort?.startDate ?? '')
  const [endDate, setEndDate] = useState(cohort?.endDate ?? '')
  const [status, setStatus] = useState<CohortStatus>(cohort?.status ?? 'draft')
  const [error, setError] = useState<string | null>(null)

  function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    if (!name.trim()) {
      setError('Nhập tên đợt phụ đạo.')
      return
    }
    if (!startDate || !endDate) {
      setError('Chọn đủ khoảng thời gian.')
      return
    }
    if (startDate > endDate) {
      setError('Ngày bắt đầu phải trước hoặc trùng ngày kết thúc.')
      return
    }

    if (isEdit && editId) {
      updateCohort(editId, { name: name.trim(), startDate, endDate, status })
    } else {
      addCohort({
        name: name.trim(),
        startDate,
        endDate,
        status,
      })
    }
    navigate('/admin/cohorts')
  }

  return (
    <div className="mx-auto max-w-xl space-y-4">
      <div>
        <Link
          to="/admin/cohorts"
          className="text-sm text-brand-500 hover:text-brand-600 no-underline hover:underline"
        >
          ← Quay lại danh sách
        </Link>
        <h1 className="mt-2 text-lg font-semibold text-gray-800">
          {isEdit ? 'Sửa đợt phụ đạo' : 'Thêm đợt phụ đạo'}
        </h1>
      </div>

      <form
        onSubmit={handleSubmit}
        className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm"
      >
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Tên đợt
          </label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Ví dụ: Đợt phụ đạo HK…"
          />
        </div>
        <div className="grid gap-4 sm:grid-cols-2">
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">
              Từ ngày
            </label>
            <input
              type="date"
              className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">
              Đến ngày
            </label>
            <input
              type="date"
              className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
            />
          </div>
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Trạng thái
          </label>
          <select
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={status}
            onChange={(e) => setStatus(e.target.value as CohortStatus)}
          >
            <option value="draft">Nháp</option>
            <option value="open">Đang mở</option>
            <option value="closed">Đã đóng</option>
          </select>
        </div>

        {error ? (
          <p className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {error}
          </p>
        ) : null}

        <div className="flex gap-2 pt-2">
          <button
            type="submit"
            className="rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600"
          >
            Lưu
          </button>
          <Link
            to="/admin/cohorts"
            className="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Hủy
          </Link>
        </div>
      </form>
    </div>
  )
}

export function CohortFormPage() {
  const { id } = useParams<{ id?: string }>()
  const { getCohort } = useDemoData()
  if (id && !getCohort(id)) {
    return <Navigate to="/admin/cohorts" replace />
  }
  return <CohortFormFields key={id ?? 'new'} editId={id} />
}
