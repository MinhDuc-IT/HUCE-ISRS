import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiStudentRegistration } from '@/shared/types/api'

export function StudentInstructorsPage() {
  const { user } = useAuth()
  const [regs, setRegs] = useState<ApiStudentRegistration[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (!user) return
    fetchRegs()
  }, [user])

  async function fetchRegs() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiStudentRegistration[] }>(
        '/student/me/remedial-registrations',
      )
      setRegs(res.data || [])
    } catch {
      setRegs([])
    } finally {
      setLoading(false)
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
        <h1 className="mt-2 text-lg font-semibold text-gray-800">Giảng viên phụ đạo</h1>
      </div>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 font-semibold">Mã MH</th>
                <th className="px-4 py-3 font-semibold">Tên môn</th>
                <th className="px-4 py-3 font-semibold">Giảng viên</th>
                <th className="px-4 py-3 font-semibold">Liên hệ GV</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan={4} className="px-4 py-8 text-center text-gray-500">
                    Đang tải...
                  </td>
                </tr>
              ) : regs.length === 0 ? (
                <tr>
                  <td colSpan={4} className="px-4 py-8 text-center text-gray-500">
                    Bạn chưa đăng ký môn nào.{' '}
                    <Link
                      to="/student/register"
                      className="text-brand-500 hover:text-brand-600 hover:underline"
                    >
                      Đăng ký phụ đạo
                    </Link>
                  </td>
                </tr>
              ) : (
                regs.map((r) => (
                  <tr key={r.id} className="hover:bg-gray-50/80">
                    <td className="px-4 py-3 font-mono text-xs">{r.course_code}</td>
                    <td className="px-4 py-3 text-gray-800">{r.course_name ?? '—'}</td>
                    <td className="px-4 py-3 text-gray-800">
                      {r.lecture_name?.trim() ? (
                        r.lecture_name
                      ) : (
                        <span className="text-gray-400">Chưa phân công</span>
                      )}
                    </td>
                    <td className="px-4 py-3 text-gray-600 text-xs">
                      {r.lecture_name?.trim() ? (
                        <>
                          <div>{r.lecturer_email || '—'}</div>
                          <div>{r.lecturer_phone || '—'}</div>
                        </>
                      ) : (
                        '—'
                      )}
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
