import { useMemo, useState, type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { useDemoData } from '@/shared/context/DemoDataContext'

export function StudentRegisterPage() {
  const { user } = useAuth()
  const {
    getOpenCohortsForRegistration,
    getCoursesByCohort,
    registerCourses,
    getRegistrationsForStudent,
  } = useDemoData()

  const openCohorts = useMemo(
    () => getOpenCohortsForRegistration(),
    [getOpenCohortsForRegistration],
  )

  const [cohortId, setCohortId] = useState<string>('')
  const [selected, setSelected] = useState<Record<string, boolean>>({})
  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)

  const activeCohortId = useMemo(() => {
    if (cohortId && openCohorts.some((c) => c.id === cohortId)) {
      return cohortId
    }
    return openCohorts[0]?.id ?? ''
  }, [cohortId, openCohorts])

  const courses = activeCohortId ? getCoursesByCohort(activeCohortId) : []

  const myCourseIdsInCohort = useMemo(() => {
    if (!user || !activeCohortId) return new Set<string>()
    return new Set(
      getRegistrationsForStudent(user.id)
        .filter((r) => r.cohortId === activeCohortId)
        .map((r) => r.courseId),
    )
  }, [user, activeCohortId, getRegistrationsForStudent])

  function toggle(courseId: string, checked: boolean) {
    setSelected((s) => ({ ...s, [courseId]: checked }))
  }

  function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setMessage(null)
    setError(null)
    if (!user || !activeCohortId) return
    const ids = Object.entries(selected)
      .filter(([, v]) => v)
      .map(([k]) => k)
    const res = registerCourses(user.id, activeCohortId, ids)
    if (!res.ok) {
      setError(
        'Không thể đăng ký: đợt không mở hoặc không trong thời gian cho phép.',
      )
      return
    }
    setSelected({})
    setMessage('Đã lưu đăng ký (mock). Xem danh sách tại “Môn đã đăng ký”.')
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
        <h1 className="mt-2 text-lg font-semibold text-gray-800">
          Đăng ký môn phụ đạo
        </h1>
        <p className="text-sm text-gray-500">
          Chỉ hiển thị đợt có trạng thái “Đang mở” và trong khoảng ngày của đợt.
        </p>
      </div>

      {openCohorts.length === 0 ? (
        <div className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
          Hiện không có đợt phụ đạo nào đang mở đăng ký. Vui lòng liên hệ quản
          trị (Admin) để mở đợt và cập nhật ngày bao gồm hôm nay.
        </div>
      ) : (
        <form
          onSubmit={handleSubmit}
          className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm"
        >
          {openCohorts.length > 1 ? (
            <div>
              <label className="mb-1 block text-sm font-medium text-gray-700">
                Chọn đợt phụ đạo
              </label>
              <select
                className="w-full max-w-md rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
                value={activeCohortId}
                onChange={(e) => {
                  setCohortId(e.target.value)
                  setSelected({})
                }}
              >
                {openCohorts.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name}
                  </option>
                ))}
              </select>
            </div>
          ) : (
            <p className="text-sm text-gray-700">
              Đợt đang mở:{' '}
              <span className="font-semibold">{openCohorts[0]?.name}</span>
            </p>
          )}

          <div>
            <p className="mb-2 text-sm font-medium text-gray-700">
              Chọn môn đăng ký
            </p>
            <ul className="divide-y divide-gray-100 rounded border border-gray-200">
              {courses.map((course) => {
                const registered = myCourseIdsInCohort.has(course.id)
                return (
                  <li
                    key={course.id}
                    className="flex items-center gap-3 px-3 py-2 text-sm"
                  >
                    <input
                      type="checkbox"
                      id={`c-${course.id}`}
                      disabled={registered}
                      checked={Boolean(selected[course.id])}
                      onChange={(e) => toggle(course.id, e.target.checked)}
                      className="h-4 w-4 rounded border-gray-300"
                    />
                    <label
                      htmlFor={`c-${course.id}`}
                      className={`flex-1 ${registered ? 'text-gray-400' : 'cursor-pointer text-gray-800'}`}
                    >
                      <span className="font-mono text-xs text-gray-500">
                        {course.code}
                      </span>{' '}
                      {course.name}
                      {registered ? (
                        <span className="ml-2 text-xs text-emerald-600">
                          (đã đăng ký)
                        </span>
                      ) : null}
                    </label>
                  </li>
                )
              })}
            </ul>
          </div>

          {error ? (
            <p className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
              {error}
            </p>
          ) : null}
          {message ? (
            <p className="rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
              {message}
            </p>
          ) : null}

          <button
            type="submit"
            className="rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600"
          >
            Lưu đăng ký
          </button>
        </form>
      )}
    </div>
  )
}
