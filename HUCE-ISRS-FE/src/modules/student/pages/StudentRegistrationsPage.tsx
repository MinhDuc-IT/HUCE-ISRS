import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { useConfirm } from '@/shared/context/ConfirmContext'
import { useToast } from '@/shared/context/ToastContext'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiRemedialTerm, ApiStudentRegistration } from '@/shared/types/api'

export function StudentRegistrationsPage() {
  const { user } = useAuth()
  const { confirm } = useConfirm()
  const { success, error: showError } = useToast()
  const [currentTermId, setCurrentTermId] = useState<number | null>(null)
  const [list, setList] = useState<ApiStudentRegistration[]>([])
  const [loading, setLoading] = useState(true)

  const fetchList = useCallback(async () => {
    try {
      setLoading(true)
      let termId: number | null = null
      try {
        const termRes = await apiFetch<{ data: ApiRemedialTerm }>(
          '/student/remedial-terms/current',
        )
        termId = termRes.data?.id ?? null
      } catch {
        termId = null
      }
      setCurrentTermId(termId)

      if (termId == null) {
        setList([])
        return
      }

      const res = await apiFetch<{ data: ApiStudentRegistration[] }>(
        `/student/me/remedial-registrations?remedial_term_id=${termId}`,
      )
      setList(res.data || [])
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      showError('Lỗi tải danh sách: ' + msg)
    } finally {
      setLoading(false)
    }
  }, [showError])

  useEffect(() => {
    if (!user) return
    void fetchList()
  }, [user, fetchList])

  async function handleRemove(regId: number) {
    const ok = await confirm({
      title: 'Hủy đăng ký',
      message: 'Hủy đăng ký môn này?',
      confirmLabel: 'Hủy đăng ký',
      variant: 'danger',
    })
    if (!ok) return
    try {
      const deleteUrl =
        currentTermId != null
          ? `/student/me/remedial-registrations/${regId}?remedial_term_id=${currentTermId}`
          : `/student/me/remedial-registrations/${regId}`
      await apiFetch(deleteUrl, { method: 'DELETE' })
      success('Đã hủy đăng ký.')
      await fetchList()
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      showError('Hủy thất bại: ' + msg)
    }
  }

  if (!user) return null

  return (
    <div className="space-y-4">
      <div>
        <Link
          to="/student"
          className="text-sm text-brand-500 hover:text-brand-600 no-underline hover:underline"
        >
          ← Trang chủ sinh viên
        </Link>
        <h1 className="mt-2 text-lg font-semibold text-gray-800">Môn đã đăng ký phụ đạo</h1>
      </div>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 font-semibold">Mã MH</th>
                <th className="px-4 py-3 font-semibold">Tên môn</th>
                <th className="px-4 py-3 font-semibold">Đăng ký lúc</th>
                <th className="px-4 py-3 text-right font-semibold">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan={4} className="px-4 py-8 text-center text-gray-500">
                    Đang tải...
                  </td>
                </tr>
              ) : list.length === 0 ? (
                <tr>
                  <td colSpan={4} className="px-4 py-8 text-center text-gray-500">
                    Chưa có môn nào.{' '}
                    <Link
                      to="/student/register"
                      className="text-brand-500 hover:text-brand-600 hover:underline"
                    >
                      Đăng ký phụ đạo
                    </Link>
                  </td>
                </tr>
              ) : (
                list.map((r) => (
                  <tr key={r.id} className="hover:bg-gray-50/80">
                    <td className="px-4 py-3 font-mono text-xs">{r.course_code}</td>
                    <td className="px-4 py-3 text-gray-800">{r.course_name ?? '—'}</td>
                    <td className="px-4 py-3 text-gray-600">
                      {r.registration_date
                        ? new Date(r.registration_date).toLocaleString('vi-VN')
                        : '—'}
                    </td>
                    <td className="px-4 py-3 text-right">
                      <button
                        type="button"
                        className="text-red-600 hover:underline"
                        onClick={() => void handleRemove(r.id)}
                      >
                        Hủy đăng ký
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
