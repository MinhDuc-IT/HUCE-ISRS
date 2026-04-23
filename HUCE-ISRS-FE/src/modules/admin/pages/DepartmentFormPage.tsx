import { useState, type FormEvent } from 'react'
import { Link, Navigate, useNavigate, useParams } from 'react-router-dom'
import { useDemoData } from '@/shared/context/DemoDataContext'

export function DepartmentFormFields({
  editId,
  cancelHref,
  afterSaveNavigateTo,
}: {
  editId: string
  cancelHref: string
  afterSaveNavigateTo: string
}) {
  const navigate = useNavigate()
  const { state, updateDepartment } = useDemoData()
  const dept = state.departments.find((d) => d.id === editId)!

  const [headEmail, setHeadEmail] = useState(dept.headEmail)
  const [headPhone, setHeadPhone] = useState(dept.headPhone)
  const [error, setError] = useState<string | null>(null)

  function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    if (!headEmail.trim()) {
      setError('Nhập email trưởng bộ môn.')
      return
    }
    updateDepartment(editId, {
      headEmail: headEmail.trim(),
      headPhone: headPhone.trim(),
    })
    navigate(afterSaveNavigateTo)
  }

  return (
    <div className="mx-auto max-w-xl space-y-4">
      <div>
        <Link
          to={cancelHref}
          className="text-sm text-brand-500 hover:text-brand-600 no-underline hover:underline"
        >
          ← Quay lại
        </Link>
        <h1 className="mt-2 text-lg font-semibold text-gray-800">
          Sửa liên hệ — {dept.name}
        </h1>
      </div>

      <form
        onSubmit={handleSubmit}
        className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm"
      >
        <div className="rounded border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600">
          <span className="font-medium text-gray-700">Mã:</span> {dept.code}
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Email trưởng bộ môn
          </label>
          <input
            type="email"
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={headEmail}
            onChange={(e) => setHeadEmail(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Số điện thoại trưởng bộ môn
          </label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={headPhone}
            onChange={(e) => setHeadPhone(e.target.value)}
          />
        </div>

        {error ? (
          <p className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {error}
          </p>
        ) : null}

        <div className="flex gap-2 pt-2">
          <button
            type="submit"
            className="rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600"
          >
            Lưu
          </button>
          <Link
            to={cancelHref}
            className="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 no-underline hover:bg-gray-50"
          >
            Hủy
          </Link>
        </div>
      </form>
    </div>
  )
}

export function DepartmentFormPage() {
  const { id } = useParams<{ id: string }>()
  const { state } = useDemoData()
  if (!id || !state.departments.some((d) => d.id === id)) {
    return <Navigate to="/admin/departments" replace />
  }
  return (
    <DepartmentFormFields
      key={id}
      editId={id}
      cancelHref="/admin/departments"
      afterSaveNavigateTo="/admin/departments"
    />
  )
}
