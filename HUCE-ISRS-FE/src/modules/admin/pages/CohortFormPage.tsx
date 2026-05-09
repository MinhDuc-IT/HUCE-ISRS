import { useState, useEffect, type FormEvent } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { apiFetch } from '@/shared/utils/apiClient'

interface Semester {
  id: number
  Name: string
  Year: number
  TermNumber: number
  IsActive: boolean
}

export function CohortFormPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id?: string }>()
  const isEdit = Boolean(id)

  // Semester list
  const [semesters, setSemesters] = useState<Semester[]>([])
  const [semesterId, setSemesterId] = useState<string>('')

  // Form fields
  const [name, setName] = useState('')
  const [startDate, setStartDate] = useState('')
  const [endDate, setEndDate] = useState('')
  const [donGia, setDonGia] = useState('150000')
  const [heSoPD, setHeSoPD] = useState('1')
  const [heSoDonGia, setHeSoDonGia] = useState('1')

  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  // New semester form (inline)
  const [showNewSemester, setShowNewSemester] = useState(false)
  const [newSemName, setNewSemName] = useState('')
  const [newSemYear, setNewSemYear] = useState(new Date().getFullYear().toString())
  const [newSemTerm, setNewSemTerm] = useState('1')
  const [creatingSem, setCreatingSem] = useState(false)

  useEffect(() => {
    fetchSemesters()
    if (isEdit && id) fetchTerm(id)
  }, [id])

  async function fetchSemesters() {
    try {
      const res = await apiFetch<{ data: Semester[] }>('/admin/semesters')
      const list = res.data || []
      setSemesters(list)
      // Auto-select active semester if not editing
      if (!isEdit && list.length > 0) {
        const active = list.find(s => s.IsActive) ?? list[0]
        setSemesterId(active.id.toString())
      }
    } catch {
      // silent
    }
  }

  async function fetchTerm(termId: string) {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: any }>(`/admin/tutoring-terms/${termId}`)
      const t = res.data
      setName(t.Name ?? '')
      setStartDate(t.StartDate?.substring(0, 10) ?? '')
      setEndDate(t.EndDate?.substring(0, 10) ?? '')
      setDonGia(t.DonGia1Tiet?.toString() ?? '150000')
      setHeSoPD(t.HeSoPD?.toString() ?? '1')
      setHeSoDonGia(t.HeSoDonGia?.toString() ?? '1')
      if (t.SemesterId) setSemesterId(t.SemesterId.toString())
    } catch {
      setError('Không tải được thông tin đợt phụ đạo.')
    } finally {
      setLoading(false)
    }
  }

  async function handleCreateSemester() {
    if (!newSemName.trim()) {
      setError('Nhập tên học kỳ.')
      return
    }
    try {
      setCreatingSem(true)
      const res = await apiFetch<{ data: Semester }>('/admin/semesters', {
        method: 'POST',
        data: {
          Name: newSemName.trim(),
          Year: parseInt(newSemYear),
          TermNumber: parseInt(newSemTerm),
          IsActive: false,
        }
      })
      const created = res.data
      setSemesters(prev => [created, ...prev])
      setSemesterId(created.id.toString())
      setShowNewSemester(false)
      setNewSemName('')
    } catch (err: any) {
      setError('Lỗi tạo học kỳ: ' + err.message)
    } finally {
      setCreatingSem(false)
    }
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)

    if (!semesterId) { setError('Chọn học kỳ cho đợt phụ đạo.'); return }
    if (!name.trim()) { setError('Nhập tên đợt phụ đạo.'); return }
    if (!startDate || !endDate) { setError('Chọn đủ ngày bắt đầu và kết thúc đợt.'); return }
    if (startDate > endDate) { setError('Ngày bắt đầu phải trước ngày kết thúc.'); return }

    const payload = {
      SemesterId: parseInt(semesterId),
      Name: name.trim(),
      StartDate: startDate,
      EndDate: endDate,
      DonGia1Tiet: parseFloat(donGia),
      HeSoPD: parseFloat(heSoPD),
      HeSoDonGia: parseFloat(heSoDonGia),
    }

    try {
      setSubmitting(true)
      if (isEdit && id) {
        await apiFetch(`/admin/tutoring-terms/${id}`, { method: 'PATCH', data: payload })
      } else {
        await apiFetch('/admin/tutoring-terms', { method: 'POST', data: payload })
      }
      navigate('/admin/cohorts')
    } catch (err: any) {
      setError(err.message || 'Lưu thất bại. Vui lòng thử lại.')
    } finally {
      setSubmitting(false)
    }
  }

  const inputClass = "w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:ring-2 focus:ring-brand-500/10"

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

      <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm">

        {/* Chọn học kỳ */}
        <div>
          <div className="flex items-center justify-between mb-1">
            <label className="text-sm font-medium text-gray-700">Học kỳ <span className="text-red-500">*</span></label>
            <button
              type="button"
              onClick={() => setShowNewSemester(!showNewSemester)}
              className="text-xs text-brand-500 hover:underline"
            >
              {showNewSemester ? '← Ẩn' : '+ Tạo học kỳ mới'}
            </button>
          </div>
          <select
            className={inputClass}
            value={semesterId}
            onChange={(e) => setSemesterId(e.target.value)}
            required
          >
            <option value="">-- Chọn học kỳ --</option>
            {semesters.map(s => (
              <option key={s.id} value={s.id}>
                {s.Name} ({s.Year} – HK{s.TermNumber}){s.IsActive ? ' ✓ Đang hoạt động' : ''}
              </option>
            ))}
          </select>

          {/* Inline form tạo học kỳ mới */}
          {showNewSemester && (
            <div className="mt-3 rounded border border-brand-200 bg-brand-50/30 p-4 space-y-3">
              <p className="text-xs font-semibold text-brand-700 uppercase tracking-wide">Tạo học kỳ mới</p>
              <div>
                <label className="text-xs text-gray-600 mb-1 block">Tên học kỳ</label>
                <input className={inputClass} value={newSemName} onChange={e => setNewSemName(e.target.value)} placeholder="VD: Học kỳ 2 năm 2024-2025" />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-xs text-gray-600 mb-1 block">Năm học</label>
                  <input type="number" className={inputClass} value={newSemYear} onChange={e => setNewSemYear(e.target.value)} min="2000" max="2100" />
                </div>
                <div>
                  <label className="text-xs text-gray-600 mb-1 block">Học kỳ số</label>
                  <select className={inputClass} value={newSemTerm} onChange={e => setNewSemTerm(e.target.value)}>
                    <option value="1">Học kỳ 1</option>
                    <option value="2">Học kỳ 2</option>
                    <option value="3">Học kỳ Hè</option>
                  </select>
                </div>
              </div>

              <button
                type="button"
                disabled={creatingSem}
                onClick={handleCreateSemester}
                className="rounded bg-brand-500 px-4 py-1.5 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-70"
              >
                {creatingSem ? 'Đang tạo...' : 'Tạo học kỳ'}
              </button>
            </div>
          )}
        </div>

        {/* Tên đợt */}
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Tên đợt <span className="text-red-500">*</span></label>
          <input className={inputClass} value={name} onChange={(e) => setName(e.target.value)} placeholder="VD: Đợt phụ đạo HK2 năm 2024" />
        </div>

        {/* Ngày bắt đầu / kết thúc */}
        <div className="grid gap-4 sm:grid-cols-2">
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Ngày bắt đầu <span className="text-red-500">*</span></label>
            <input type="date" className={inputClass} value={startDate} onChange={(e) => setStartDate(e.target.value)} />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Ngày kết thúc <span className="text-red-500">*</span></label>
            <input type="date" className={inputClass} value={endDate} onChange={(e) => setEndDate(e.target.value)} />
          </div>
        </div>

        {/* Hệ số tài chính */}
        <div className="grid gap-4 sm:grid-cols-3">
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Đơn giá 1 tiết (VNĐ)</label>
            <input type="number" className={inputClass} value={donGia} onChange={(e) => setDonGia(e.target.value)} min="0" />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Hệ số PD</label>
            <input type="number" step="0.1" className={inputClass} value={heSoPD} onChange={(e) => setHeSoPD(e.target.value)} min="0" />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Hệ số đơn giá</label>
            <input type="number" step="0.1" className={inputClass} value={heSoDonGia} onChange={(e) => setHeSoDonGia(e.target.value)} min="0" />
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
          <Link to="/admin/cohorts" className="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50">
            Hủy
          </Link>
        </div>
      </form>
    </div>
  )
}
