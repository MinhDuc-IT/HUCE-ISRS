import { LecturerAssignmentTable } from '@/shared/components/LecturerAssignmentTable'
import { useAuth } from '@/shared/context/AuthContext'

export function DepartmentAssignmentsPage() {
  const { user } = useAuth()
  const deptId = user?.departmentId

  if (!deptId) {
    return (
      <p className="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Tài khoản chưa gắn bộ môn. Đăng xuất và đăng nhập lại bằng{' '}
        <code className="rounded bg-white px-1">bomon</code>.
      </p>
    )
  }

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-lg font-semibold text-gray-800">
          Phân công giảng viên phụ đạo
        </h1>
      </div>
      <LecturerAssignmentTable departmentIdFilter={deptId} />
    </div>
  )
}
