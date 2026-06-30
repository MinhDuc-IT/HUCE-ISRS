import { useState, useEffect, type FormEvent } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiRemedialTerm } from '@/shared/types/api'

function dateInputValue(iso: string | null | undefined): string {
  return (iso ?? '').substring(0, 10)
}

function addDays(dateStr: string, days: number): string {
  const [y, m, d] = dateStr.split('-').map(Number)
  if (!y || !m || !d) return ''
  const date = new Date(y, m - 1, d)
  date.setDate(date.getDate() + days)
  const yy = date.getFullYear()
  const mm = String(date.getMonth() + 1).padStart(2, '0')
  const dd = String(date.getDate()).padStart(2, '0')
  return `${yy}-${mm}-${dd}`
}

const REGISTRATION_WINDOW_DAYS = 14

export function CohortFormPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id?: string }>()
  const isEdit = Boolean(id)

  const [year, setYear] = useState(new Date().getFullYear().toString())
  const [semester, setSemester] = useState('1')
  const [name, setName] = useState('')
  const [startDate, setStartDate] = useState('')
  const [endDate, setEndDate] = useState('')
  const [registrationStart, setRegistrationStart] = useState('')
  const [registrationEnd, setRegistrationEnd] = useState('')
  // const [isCurrentTerm, setIsCurrentTerm] = useState(!isEdit) // legacy: đợt hiện tại theo status, không còn set qua form
  const [donGia, setDonGia] = useState('150000')
  const [heSoPD, setHeSoPD] = useState('1')
  const [heSoDonGia, setHeSoDonGia] = useState('1')

  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    if (isEdit && id) fetchTerm(id)
  }, [id, isEdit])

  async function fetchTerm(termId: string) {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiRemedialTerm }>(`/admin/remedial-terms/${termId}`)
      const t = res.data
      setYear(String(t.year ?? new Date().getFullYear()))
      setSemester(String(t.semester ?? 1))
      setName(t.name ?? '')
      setStartDate(dateInputValue(t.start_date))
      setEndDate(dateInputValue(t.end_date))
      setRegistrationStart(dateInputValue(t.registration_start))
      setRegistrationEnd(dateInputValue(t.registration_end))
      // setIsCurrentTerm(Boolean(t.is_current_term))
      setDonGia(String(t.price_per_period ?? '150000'))
      setHeSoPD(String(t.remedial_coefficient ?? '1'))
      setHeSoDonGia(String(t.price_coefficient ?? '1'))
    } catch {
      setError('Không tải được thông tin đợt phụ đạo.')
    } finally {
      setLoading(false)
    }
  }

  function syncRegistrationEndFromStart(openDate: string) {
    if (openDate) {
      setRegistrationEnd(addDays(openDate, REGISTRATION_WINDOW_DAYS))
    }
  }

  function handleStartDateChange(value: string) {
    setStartDate(value)
    if (value) {
      setRegistrationStart(value)
      syncRegistrationEndFromStart(value)
    }
  }

  function handleEndDateChange(value: string) {
    setEndDate(value)
  }

  function handleRegistrationStartChange(value: string) {
    setRegistrationStart(value)
    if (value) {
      syncRegistrationEndFromStart(value)
    }
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)

    if (!name.trim()) {
      setError('Nhập tên đợt phụ đạo.')
      return
    }
    if (!startDate || !endDate) {
      setError('Chọn đủ ngày bắt đầu và kết thúc đợt.')
      return
    }
    if (startDate > endDate) {
      setError('Ngày bắt đầu đợt phải trước hoặc bằng ngày kết thúc.')
      return
    }
    if (!registrationStart || !registrationEnd) {
      setError('Chọn đủ ngày mở và đóng đăng ký.')
      return
    }
    if (registrationStart > registrationEnd) {
      setError('Ngày mở đăng ký phải trước hoặc bằng ngày đóng đăng ký.')
      return
    }

    const payload = {
      year: parseInt(year, 10),
      semester: parseInt(semester, 10),
      name: name.trim(),
      start_date: startDate,
      end_date: endDate,
      registration_start: registrationStart,
      registration_end: registrationEnd,
      price_per_period: parseInt(donGia, 10),
      remedial_coefficient: parseInt(heSoPD, 10),
      price_coefficient: parseFloat(heSoDonGia),
      // is_current_term: isCurrentTerm, // legacy — BE xác định đợt hiện tại qua status
    }

    try {
      setSubmitting(true)
      if (isEdit && id) {
        await apiFetch(`/admin/remedial-terms/${id}`, { method: 'PATCH', data: payload })
      } else {
        await apiFetch('/admin/remedial-terms', { method: 'POST', data: payload })
      }
      navigate('/admin/cohorts')
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : 'Lưu thất bại. Vui lòng thử lại.'
      setError(message)
    } finally {
      setSubmitting(false)
    }
  }

  const inputClass =
    'w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:ring-2 focus:ring-brand-500/10'

  const dateInputClass = `${inputClass} cursor-pointer min-h-[2.5rem]`

  if (loading) return <p className="text-center text-gray-500 py-10">Đang tải...</p>

  return (
    <div className="mx-auto max-w-2xl space-y-4">
      <div>
        <Link to="/admin/cohorts" className="text-sm text-brand-500 hover:text-brand-600 no-underline hover:underline">
          ← Quay lại danh sách
        </Link>
        <h1 className="mt-2 text-lg font-semibold text-gray-800">
          {isEdit ? 'Sửa đợt phụ đạo' : 'Thêm đợt phụ đạo'}
        </h1>
      </div>

      <form onSubmit={handleSubmit} className="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm">
        <div className="grid gap-4 sm:grid-cols-2">
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">
              Năm học <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              className={inputClass}
              value={year}
              onChange={(e) => setYear(e.target.value)}
              min="2000"
              max="2100"
              required
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">
              Học kỳ <span className="text-red-500">*</span>
            </label>
            <select className={inputClass} value={semester} onChange={(e) => setSemester(e.target.value)} required>
              <option value="1">Học kỳ 1</option>
              <option value="2">Học kỳ 2</option>
              <option value="3">Học kỳ Hè</option>
            </select>
          </div>
        </div>

        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Tên đợt <span className="text-red-500">*</span>
          </label>
          <input
            className={inputClass}
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="VD: Đợt phụ đạo HK2 năm 2024"
          />
        </div>

        <div className="space-y-3">
          <p className="text-sm font-semibold text-gray-700">Thời gian đợt phụ đạo</p>
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label className="mb-1 block text-sm font-medium text-gray-700">
                Ngày bắt đầu <span className="text-red-500">*</span>
              </label>
              <input
                type="date"
                className={dateInputClass}
                value={startDate}
                onChange={(e) => handleStartDateChange(e.target.value)}
              />
            </div>
            <div>
              <label className="mb-1 block text-sm font-medium text-gray-700">
                Ngày kết thúc <span className="text-red-500">*</span>
              </label>
              <input
                type="date"
                className={dateInputClass}
                value={endDate}
                onChange={(e) => handleEndDateChange(e.target.value)}
              />
            </div>
          </div>
        </div>

        <div className="space-y-3">
          <p className="text-sm font-semibold text-gray-700">Thời gian đăng ký sinh viên</p>
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label className="mb-1 block text-sm font-medium text-gray-700">
                Mở đăng ký <span className="text-red-500">*</span>
              </label>
              <input
                type="date"
                className={dateInputClass}
                value={registrationStart}
                onChange={(e) => handleRegistrationStartChange(e.target.value)}
              />
            </div>
            <div>
              <label className="mb-1 block text-sm font-medium text-gray-700">
                Đóng đăng ký <span className="text-red-500">*</span>
              </label>
              <input
                type="date"
                className={dateInputClass}
                value={registrationEnd}
                onChange={(e) => setRegistrationEnd(e.target.value)}
              />
            </div>
          </div>
        </div>

        {/* legacy: đợt hiện tại được xác định qua trạng thái (status), không còn bật từ form tạo/sửa
        <label className="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
          <input
            type="checkbox"
            className="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
            checked={isCurrentTerm}
            onChange={(e) => setIsCurrentTerm(e.target.checked)}
          />
          <span>
            <span className="block text-sm font-medium text-gray-800">Đặt làm đợt hiện tại</span>
            <span className="mt-0.5 block text-xs text-gray-500">
              Sinh viên chỉ đăng ký môn trên đợt được đánh dấu hiện tại. Chỉ nên có một đợt hiện tại.
            </span>
          </span>
        </label>
        */}

        <div className="grid gap-4 sm:grid-cols-3">
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Đơn giá 1 tiết (VNĐ)</label>
            <input type="number" className={inputClass} value={donGia} onChange={(e) => setDonGia(e.target.value)} min="0" />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Hệ số PD</label>
            <input
              type="number"
              step="0.1"
              className={inputClass}
              value={heSoPD}
              onChange={(e) => setHeSoPD(e.target.value)}
              min="0"
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Hệ số đơn giá</label>
            <input
              type="number"
              step="0.1"
              className={inputClass}
              value={heSoDonGia}
              onChange={(e) => setHeSoDonGia(e.target.value)}
              min="0"
            />
          </div>
        </div>

        {error && (
          <p className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p>
        )}

        <div className="flex gap-2 pt-2">
          <button
            type="submit"
            disabled={submitting}
            className="rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-70"
          >
            {submitting ? 'Đang lưu...' : 'Lưu'}
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
