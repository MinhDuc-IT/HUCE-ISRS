import { useEffect, useState } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'
import type {
  ApiAdminRegistrationStudent,
  ApiAdminRegistrationSummary,
  ApiRemedialTerm,
} from '@/shared/types/api'

function formatDateVi(value: string | null | undefined): string {
  if (!value) return '—'
  return new Date(value).toLocaleDateString('vi-VN')
}

export function AdminRegistrationsPage() {
  const [terms, setTerms] = useState<ApiRemedialTerm[]>([])
  const [termId, setTermId] = useState('')
  const [rows, setRows] = useState<ApiAdminRegistrationSummary[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const [selectedRow, setSelectedRow] = useState<ApiAdminRegistrationSummary | null>(null)
  const [detailRows, setDetailRows] = useState<ApiAdminRegistrationStudent[]>([])
  const [detailLoading, setDetailLoading] = useState(false)
  const [detailError, setDetailError] = useState<string | null>(null)

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
      const res = await apiFetch<{ data: ApiAdminRegistrationSummary[] }>(
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

  useEffect(() => {
    fetchTerms()
  }, [])

  useEffect(() => {
    fetchRows()
  }, [termId])

  async function openDetailModal(row: ApiAdminRegistrationSummary) {
    setSelectedRow(row)
    setDetailRows([])
    setDetailError(null)
    setDetailLoading(true)

    try {
      const res = await apiFetch<{ data: ApiAdminRegistrationStudent[] }>(
        `/admin/remedial-registrations/students?remedial_term_id=${row.remedial_term_id}&subject_id=${row.subject_id}`,
      )
      setDetailRows(res.data || [])
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setDetailError('Lỗi tải danh sách sinh viên: ' + msg)
    } finally {
      setDetailLoading(false)
    }
  }

  function closeDetailModal() {
    setSelectedRow(null)
    setDetailRows([])
    setDetailError(null)
  }

  return (
    <div className="space-y-4">
      <h1 className="text-lg font-semibold text-gray-800">Tra cứu đăng ký phụ đạo</h1>
      <p className="text-sm text-gray-500">
        Danh sách tổng hợp theo đợt và môn học. Bấm vào một dòng để xem chi tiết sinh viên đăng ký.
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
              <th className="px-3 py-2">Đợt phụ đạo</th>
              <th className="px-3 py-2">Mã môn</th>
              <th className="px-3 py-2">Tên môn</th>
              <th className="px-3 py-2 text-right">Số sinh viên</th>
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
                <tr
                  key={`${row.remedial_term_id}-${row.subject_id}`}
                  className="cursor-pointer border-t border-gray-100 hover:bg-blue-50"
                  onClick={() => openDetailModal(row)}
                >
                  <td className="px-3 py-2">{row.remedial_term_name}</td>
                  <td className="px-3 py-2 font-mono text-xs">{row.subject_code}</td>
                  <td className="px-3 py-2">{row.subject_name}</td>
                  <td className="px-3 py-2 text-right tabular-nums">{row.student_count}</td>
                  <td className="px-3 py-2">{row.lecture_name || '—'}</td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {selectedRow && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
          onMouseDown={(e) => {
            if (e.target === e.currentTarget) closeDetailModal()
          }}
          role="dialog"
          aria-modal="true"
        >
          <div className="flex max-h-[85vh] w-full max-w-3xl flex-col rounded border border-blue-300 bg-white shadow-lg">
            <div className="rounded-t bg-blue-600 px-4 py-3 text-center text-white">
              <div className="font-semibold">{selectedRow.remedial_term_name}</div>
              <div className="text-sm opacity-90">{selectedRow.subject_name}</div>
            </div>

            <div className="flex-1 overflow-auto p-4">
              {detailError && (
                <div className="mb-3 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                  {detailError}
                </div>
              )}

              <table className="w-full text-sm text-left">
                <thead className="bg-gray-100 text-xs uppercase text-gray-600">
                  <tr>
                    <th className="px-3 py-2">Mã sinh viên</th>
                    <th className="px-3 py-2">Tên sinh viên</th>
                    <th className="px-3 py-2">Lớp</th>
                    <th className="px-3 py-2">Ngày đăng ký</th>
                  </tr>
                </thead>
                <tbody>
                  {detailLoading ? (
                    <tr>
                      <td colSpan={4} className="px-3 py-6 text-center text-gray-500">
                        Đang tải...
                      </td>
                    </tr>
                  ) : detailRows.length === 0 ? (
                    <tr>
                      <td colSpan={4} className="px-3 py-6 text-center text-gray-500 italic">
                        Không có sinh viên nào.
                      </td>
                    </tr>
                  ) : (
                    detailRows.map((student) => (
                      <tr
                        key={student.id}
                        className="border-t border-gray-100 hover:bg-gray-50"
                      >
                        <td className="px-3 py-2 font-mono text-xs">
                          {student.student_code || '—'}
                        </td>
                        <td className="px-3 py-2">{student.student_name || '—'}</td>
                        <td className="px-3 py-2">{student.class_name || '—'}</td>
                        <td className="px-3 py-2">
                          {formatDateVi(student.registration_date)}
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            <div className="flex justify-end border-t border-gray-200 px-4 py-3">
              <button
                type="button"
                className="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                onClick={closeDetailModal}
              >
                Đóng
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
