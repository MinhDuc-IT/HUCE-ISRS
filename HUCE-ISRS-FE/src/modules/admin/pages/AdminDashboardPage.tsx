import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiTermStatistics } from '@/shared/types/api'

interface DashboardStats {
  term_count: number
  user_count: number
  assigned_class_count: number
  total_enrollments: number
}

export function AdminDashboardPage() {
  const [stats, setStats] = useState<DashboardStats | null>(null)
  const [loading, setLoading] = useState(true)

  async function fetchStats() {
    try {
      setLoading(true)
      const [termsRes, usersRes] = await Promise.all([
        apiFetch<{ data: { terms: { id: number; name: string }[] } }>('/admin/statistics/terms'),
        apiFetch<{ data: unknown[] }>('/admin/users'),
      ])

      const terms = termsRes.data?.terms || []
      const users = usersRes.data || []

      let assignedClassCount = 0
      let totalEnrollments = 0
      if (terms.length > 0) {
        const latestTerm = terms[0]
        try {
          const statsRes = await apiFetch<{ data: ApiTermStatistics }>(
            `/admin/statistics/terms/${latestTerm.id}`,
          )
          assignedClassCount = statsRes.data?.assigned_class_count ?? 0
          totalEnrollments = statsRes.data?.distinct_student_count ?? 0
        } catch {
          // silent
        }
      }

      setStats({
        term_count: terms.length,
        user_count: users.length,
        assigned_class_count: assignedClassCount,
        total_enrollments: totalEnrollments,
      })
    } catch {
      setStats({ term_count: 0, user_count: 0, assigned_class_count: 0, total_enrollments: 0 })
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchStats()
  }, [])

  const navLinks = [
    { to: '/admin/cohorts', label: 'Đợt phụ đạo', primary: true },
    { to: '/admin/users', label: 'Người dùng' },
    { to: '/admin/departments', label: 'Bộ môn' },
    { to: '/admin/registrations', label: 'Tra cứu đăng ký' },
    { to: '/admin/email-department', label: 'Gửi email BM' },
    { to: '/admin/settings', label: 'Cài đặt' },
    { to: '/admin/statistics', label: 'Thống kê đợt' },
  ]

  return (
    <div className="space-y-4">
      <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm">
        <h1 className="text-lg font-semibold text-gray-800">Trang quản trị</h1>
        <div className="mt-4 flex flex-wrap gap-2">
          {navLinks.map((l) => (
            <Link
              key={l.to}
              to={l.to}
              className={
                l.primary
                  ? 'inline-flex rounded bg-brand-500 px-3 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600'
                  : 'inline-flex rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50'
              }
            >
              {l.label}
            </Link>
          ))}
        </div>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {[
          { label: 'Đợt phụ đạo', value: stats?.term_count, note: 'Tổng số đợt trong hệ thống' },
          { label: 'Người dùng', value: stats?.user_count, note: 'Admin, Bộ môn, Sinh viên' },
          { label: 'Đã phân công GV', value: stats?.assigned_class_count, note: 'Đợt gần nhất' },
          { label: 'Sinh viên đã đăng ký', value: stats?.total_enrollments, note: 'Đợt gần nhất' },
        ].map((card) => (
          <div key={card.label} className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">{card.label}</p>
            <p className="mt-1 text-2xl font-semibold text-gray-800">
              {loading ? '—' : (card.value ?? 0)}
            </p>
            <p className="mt-1 text-xs text-gray-400">{card.note}</p>
          </div>
        ))}
      </div>
    </div>
  )
}
