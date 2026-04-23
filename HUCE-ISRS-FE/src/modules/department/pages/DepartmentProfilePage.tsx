import { Navigate } from 'react-router-dom'
import { DepartmentFormFields } from '@/modules/admin/pages/DepartmentFormPage'
import { useAuth } from '@/shared/context/AuthContext'
import { useDemoData } from '@/shared/context/DemoDataContext'

export function DepartmentProfilePage() {
  const { user } = useAuth()
  const { state } = useDemoData()
  const deptId = user?.departmentId

  if (!deptId) {
    return (
      <p className="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Tài khoản chưa gắn bộ môn. Hãy đăng xuất và đăng nhập lại bằng{' '}
        <code className="rounded bg-white px-1">bomon</code> /{' '}
        <code className="rounded bg-white px-1">bomon</code> (seed).
      </p>
    )
  }

  if (!state.departments.some((d) => d.id === deptId)) {
    return <Navigate to="/department" replace />
  }

  return (
    <div className="space-y-4">
      <h1 className="text-lg font-semibold text-gray-800">
        Thông tin bộ môn của tôi
      </h1>
      <DepartmentFormFields
        key={deptId}
        editId={deptId}
        cancelHref="/department"
        afterSaveNavigateTo="/department"
      />
    </div>
  )
}
