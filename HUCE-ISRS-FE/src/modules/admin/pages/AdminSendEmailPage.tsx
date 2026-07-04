import { useEffect, useState, type FormEvent } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiDepartment } from '@/shared/types/api'

export function AdminSendEmailPage() {
  const [departments, setDepartments] = useState<ApiDepartment[]>([])
  const [departmentId, setDepartmentId] = useState('')
  const [subject, setSubject] = useState('Thông báo phụ đạo — danh sách môn/SV')
  const [body, setBody] = useState(
    'Kính gửi Trưởng bộ môn,\n\nVui lòng xem danh sách môn phụ đạo và sinh viên đăng ký trong hệ thống.\n',
  )
  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)
  const [sending, setSending] = useState(false)

  async function fetchDepartments() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiDepartment[] }>('/admin/departments')
      const list = res.data || []
      setDepartments(list)
      if (list.length > 0) {
        setDepartmentId(String(list[0].id))
      }
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Không tải danh sách bộ môn: ' + msg)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchDepartments()
  }, [])

  const selectedDept = departments.find((d) => String(d.id) === departmentId)

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setMessage(null)
    setError(null)

    if (!departmentId) {
      setError('Chọn bộ môn.')
      return
    }

    try {
      setSending(true)
      await apiFetch(`/admin/departments/${departmentId}/send-email`, {
        method: 'POST',
        data: { subject, body },
      })
      setMessage('Đã gửi email tổng hợp về bộ môn thành công.')
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Gửi email thất bại.'
      setError(msg)
    } finally {
      setSending(false)
    }
  }

  if (loading) {
    return <p className="text-sm text-gray-500">Đang tải...</p>
  }

  return (
    <div className="mx-auto max-w-2xl space-y-4">
      <h1 className="text-lg font-semibold text-gray-800">Gửi email về bộ môn</h1>

      <form
        onSubmit={handleSubmit}
        className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm"
      >
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Bộ môn</label>
          <select
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            value={departmentId}
            onChange={(e) => setDepartmentId(e.target.value)}
          >
            {departments.map((d) => (
              <option key={d.id} value={d.id}>
                {d.department_name} ({d.department_code})
              </option>
            ))}
          </select>
          {selectedDept?.email && (
            <p className="mt-1 text-xs text-gray-500">Email bộ môn: {selectedDept.email}</p>
          )}
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Tiêu đề</label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            value={subject}
            onChange={(e) => setSubject(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">Nội dung</label>
          <textarea
            rows={8}
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
            value={body}
            onChange={(e) => setBody(e.target.value)}
          />
        </div>

        {error && (
          <p className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {error}
          </p>
        )}
        {message && (
          <p className="rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
            {message}
          </p>
        )}

        <button
          type="submit"
          disabled={sending || departments.length === 0}
          className="rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-70"
        >
          {sending ? 'Đang gửi...' : 'Gửi email'}
        </button>
      </form>
    </div>
  )
}
