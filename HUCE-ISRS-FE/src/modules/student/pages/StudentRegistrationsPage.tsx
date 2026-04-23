import { Link } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { useConfirm } from '@/shared/context/ConfirmContext'
import { useDemoData } from '@/shared/context/DemoDataContext'
import { useToast } from '@/shared/context/ToastContext'

export function StudentRegistrationsPage() {
  const { user } = useAuth()
  const { getRegistrationsForStudent, getCohort, getCourse, removeRegistration } =
    useDemoData()
  const { confirm } = useConfirm()
  const { success } = useToast()

  if (!user) return null

  const studentId = user.id
  const list = getRegistrationsForStudent(studentId)

  async function handleRemove(regId: string) {
    const ok = await confirm({
      title: 'Hủy đăng ký',
      message: 'Hủy đăng ký môn này? (mock, lưu session)',
      confirmLabel: 'Hủy đăng ký',
      variant: 'danger',
    })
    if (!ok) return
    removeRegistration(regId, studentId)
    success('Đã hủy đăng ký.')
  }

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
          Môn đã đăng ký phụ đạo
        </h1>
      </div>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 font-semibold">Đợt</th>
                <th className="px-4 py-3 font-semibold">Môn</th>
                <th className="px-4 py-3 font-semibold">Đăng ký lúc</th>
                <th className="px-4 py-3 text-right font-semibold">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {list.map((r) => {
                const cohort = getCohort(r.cohortId)
                const course = getCourse(r.courseId)
                return (
                  <tr key={r.id} className="hover:bg-gray-50/80">
                    <td className="px-4 py-3 text-gray-800">
                      {cohort?.name ?? r.cohortId}
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
                        r.courseId
                      )}
                    </td>
                    <td className="px-4 py-3 text-gray-600">
                      {new Date(r.createdAt).toLocaleString('vi-VN')}
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
                )
              })}
            </tbody>
          </table>
        </div>
        {list.length === 0 ? (
          <p className="px-4 py-8 text-center text-sm text-gray-500">
            Chưa có môn nào đã đăng ký.{' '}
            <Link to="/student/register" className="text-brand-500 hover:text-brand-600 hover:underline">
              Đăng ký phụ đạo
            </Link>
          </p>
        ) : null}
      </div>
    </div>
  )
}
