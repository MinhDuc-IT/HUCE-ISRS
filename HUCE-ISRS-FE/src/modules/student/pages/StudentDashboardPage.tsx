import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/shared/utils/apiClient'
import { useAuth } from '@/shared/context/AuthContext'

export function StudentDashboardPage() {
  const { user } = useAuth()
  const [regCount, setRegCount] = useState<number | null>(null)
  const [openTermCount, setOpenTermCount] = useState<number | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (!user) return
    fetchData()
  }, [user])

  async function fetchData() {
    try {
      setLoading(true)
      // Fetch registrations for this student
      const [regsRes, termsRes] = await Promise.all([
        apiFetch<{ data: any[] }>(`/students/${user!.id}/registrations`),
        apiFetch<{ data: any[] }>('/admin/statistics/terms'),
      ])
      setRegCount(regsRes.data?.length ?? 0)
      setOpenTermCount(termsRes.data?.length ?? 0)
    } catch {
      setRegCount(0)
      setOpenTermCount(0)
    } finally {
      setLoading(false)
    }
  }

  if (!user) return null

  return (
    <div className="space-y-4">
      <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm">
        <h1 className="text-lg font-semibold text-gray-800">
          Xin chào, {user.displayName}
        </h1>
        <div className="mt-4 flex flex-wrap gap-3">
          <Link
            to="/student/register"
            className="inline-flex rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600"
          >
            Đăng ký phụ đạo
          </Link>
          <Link
            to="/student/registrations"
            className="inline-flex rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
          >
            Môn đã đăng ký
          </Link>
          <Link
            to="/student/instructors"
            className="inline-flex rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
          >
            Giảng viên phụ đạo
          </Link>
        </div>
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
            Đợt hiện có trong hệ thống
          </p>
          <p className="mt-1 text-2xl font-semibold text-gray-800">
            {loading ? '—' : openTermCount}
          </p>
        </div>
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
          <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
            Môn bạn đã đăng ký
          </p>
          <p className="mt-1 text-2xl font-semibold text-gray-800">
            {loading ? '—' : regCount}
          </p>
        </div>
      </div>
    </div>
  )
}
