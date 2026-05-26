import { useEffect, useState } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiAdminRegistration, ApiRemedialTerm } from '@/shared/types/api'

export function AdminRegistrationsPage() {
  const [terms, setTerms] = useState<ApiRemedialTerm[]>([])
  const [termId, setTermId] = useState('')
  const [rows, setRows] = useState<ApiAdminRegistration[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    fetchTerms()
  }, [])

  useEffect(() => {
    fetchRows()
  }, [termId])

  async function fetchTerms() {
    try {
      const res = await apiFetch<{ data: ApiRemedialTerm[] }>('/admin/remedial-terms')
      setTerms(res.data || [])
    } catch {
      setTerms([])
    }
  }

  async function fetchRows() {
    try {
      setLoading(true)
      setError(null)
      const query = termId ? `?remedial_term_id=${termId}` : ''
      const res = await apiFetch<{ data: ApiAdminRegistration[] }>(
        `/admin/remedial-registrations${query}`,
      )
      setRows(res.data || [])
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Lỗi tải danh sách: ' + msg)
      setRows([])
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-4">
      <h1 className="text-lg font-semibold text-gray-800">Tra cứu đăng ký phụ đạo</h1>
      <p className="text-sm text-gray-500">
        Phân công giảng viên do trưởng bộ môn thực hiện tại khu vực Bộ môn.
      </p>

      <div className="max-w-xs">
        <label className="mb-1 block text-sm font-medium text-gray-700">Lọc theo đợt</label>
        <select
          className="w-full rounded border border-gray-300 px-3 py-2 text-sm"
          value={termId}
          onChange={(e) => setTermId(e.target.value)}
        >
          <option value="">Tất cả đợt</option>
          {terms.map((t) => (
            <option key={t.id} value={t.id}>
              {t.name}
            </option>
          ))}
        </select>
      </div>

      {error && (
        <div className="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      <div className="overflow-x-auto rounded border border-gray-200 bg-white shadow-sm">
        <table className="w-full text-sm text-left">
          <thead className="bg-gray-100 text-xs uppercase text-gray-600">
            <tr>
              <th className="px-3 py-2">Mã SV</th>
              <th className="px-3 py-2">Họ tên</th>
              <th className="px-3 py-2">Môn</th>
              <th className="px-3 py-2">Đợt</th>
              <th className="px-3 py-2">GV phụ đạo</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr>
                <td colSpan={5} className="px-3 py-6 text-center text-gray-500">
                  Đang tải...
                </td>
              </tr>
            ) : rows.length === 0 ? (
              <tr>
                <td colSpan={5} className="px-3 py-6 text-center text-gray-500 italic">
                  Không có đăng ký nào.
                </td>
              </tr>
            ) : (
              rows.map((row) => (
                <tr key={row.id} className="border-t border-gray-100 hover:bg-gray-50">
                  <td className="px-3 py-2 font-mono text-xs">{row.student_code}</td>
                  <td className="px-3 py-2">{row.student_name}</td>
                  <td className="px-3 py-2">
                    {row.subject_code}
                    <span className="block text-xs text-gray-500">{row.subject_name}</span>
                  </td>
                  <td className="px-3 py-2 text-xs">{row.remedial_term_name ?? '—'}</td>
                  <td className="px-3 py-2 text-xs">{row.lecture_name || '—'}</td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}
