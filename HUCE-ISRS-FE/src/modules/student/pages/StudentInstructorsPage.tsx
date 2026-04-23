import { Link } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { useDemoData } from '@/shared/context/DemoDataContext'

export function StudentInstructorsPage() {
  const { user } = useAuth()
  const {
    getRegistrationsForStudent,
    getCohort,
    getCourse,
    getDepartment,
    getLecturerAssignment,
  } = useDemoData()

  if (!user) return null

  const regs = getRegistrationsForStudent(user.id)

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
          Giảng viên phụ đạo
        </h1>
      </div>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 font-semibold">Đợt</th>
                <th className="px-4 py-3 font-semibold">Môn</th>
                <th className="px-4 py-3 font-semibold">Bộ môn</th>
                <th className="px-4 py-3 font-semibold">Giảng viên</th>
                <th className="px-4 py-3 font-semibold">Liên hệ GV</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {regs.map((r) => {
                const cohort = getCohort(r.cohortId)
                const course = getCourse(r.courseId)
                const dept = course
                  ? getDepartment(course.departmentId)
                  : undefined
                const asg = getLecturerAssignment(r.cohortId, r.courseId)
                return (
                  <tr key={r.id} className="hover:bg-gray-50/80">
                    <td className="px-4 py-3 text-gray-700">
                      {cohort?.name ?? '—'}
                    </td>
                    <td className="px-4 py-3 text-gray-800">
                      {course ? (
                        <>
                          <span className="font-mono text-xs text-gray-500">
                            {course.code}
                          </span>{' '}
                          {course.name}
                        </>
                      ) : (
                        '—'
                      )}
                    </td>
                    <td className="px-4 py-3 text-gray-600">
                      {dept?.name ?? '—'}
                    </td>
                    <td className="px-4 py-3 text-gray-800">
                      {asg?.lecturerName?.trim() ? (
                        asg.lecturerName
                      ) : (
                        <span className="text-gray-400">Chưa phân công</span>
                      )}
                    </td>
                    <td className="px-4 py-3 text-gray-600">
                      {asg?.lecturerName?.trim() ? (
                        <div className="text-xs">
                          <div>{asg.lecturerEmail || '—'}</div>
                          <div>{asg.lecturerPhone || '—'}</div>
                        </div>
                      ) : (
                        '—'
                      )}
                    </td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        </div>
        {regs.length === 0 ? (
          <p className="px-4 py-8 text-center text-sm text-gray-500">
            Bạn chưa đăng ký môn nào.{' '}
            <Link to="/student/register" className="text-brand-500 hover:text-brand-600 hover:underline">
              Đăng ký phụ đạo
            </Link>
          </p>
        ) : null}
      </div>
    </div>
  )
}
