import { useMemo, useState } from 'react'
import { useDemoData } from '@/shared/context/DemoDataContext'
import type { Cohort, Course, Department } from '@/shared/types/remedial'

type LecturerAssignmentTableProps = {
  /** `null` = Admin xem tất cả; string = chỉ môn thuộc bộ môn đó. */
  departmentIdFilter: string | null
}

export function LecturerAssignmentTable({
  departmentIdFilter,
}: LecturerAssignmentTableProps) {
  const { listCoursesWithMeta } = useDemoData()

  const rows = useMemo(
    () => listCoursesWithMeta(departmentIdFilter),
    [departmentIdFilter, listCoursesWithMeta],
  )

  if (rows.length === 0) {
    return (
      <p className="rounded border border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-600">
        Không có môn phụ đạo trong phạm vi bộ môn này.
      </p>
    )
  }

  return (
    <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
      <div className="overflow-x-auto">
        <table className="min-w-full border-collapse text-left text-sm">
          <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
            <tr>
              <th className="px-3 py-2 font-semibold">Đợt</th>
              <th className="px-3 py-2 font-semibold">Môn</th>
              {departmentIdFilter ? null : (
                <th className="px-3 py-2 font-semibold">Bộ môn</th>
              )}
              <th className="px-3 py-2 font-semibold">Giảng viên</th>
              <th className="px-3 py-2 font-semibold">Email GV</th>
              <th className="px-3 py-2 font-semibold">SĐT GV</th>
              <th className="px-3 py-2 text-right font-semibold">Lưu</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {rows.map(({ cohort, course, department }) => (
              <AssignmentRow
                key={`${cohort.id}-${course.id}`}
                cohort={cohort}
                course={course}
                department={department}
                showDepartmentColumn={!departmentIdFilter}
              />
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}

function AssignmentRow({
  cohort,
  course,
  department,
  showDepartmentColumn,
}: {
  cohort: Cohort
  course: Course
  department: Department
  showDepartmentColumn: boolean
}) {
  const { getLecturerAssignment, upsertLecturerAssignment } = useDemoData()
  const existing = getLecturerAssignment(cohort.id, course.id)
  const [lecturerName, setLecturerName] = useState(existing?.lecturerName ?? '')
  const [lecturerEmail, setLecturerEmail] = useState(
    existing?.lecturerEmail ?? '',
  )
  const [lecturerPhone, setLecturerPhone] = useState(
    existing?.lecturerPhone ?? '',
  )
  const [savedFlash, setSavedFlash] = useState(false)

  function handleSave() {
    upsertLecturerAssignment(cohort.id, course.id, {
      lecturerName: lecturerName.trim(),
      lecturerEmail: lecturerEmail.trim(),
      lecturerPhone: lecturerPhone.trim(),
    })
    setSavedFlash(true)
    window.setTimeout(() => setSavedFlash(false), 1200)
  }

  return (
    <tr className="align-top hover:bg-gray-50/80">
      <td className="px-3 py-2 text-gray-700">{cohort.name}</td>
      <td className="px-3 py-2 text-gray-800">
        <span className="font-mono text-xs text-gray-500">{course.code}</span>{' '}
        {course.name}
      </td>
      {showDepartmentColumn ? (
        <td className="px-3 py-2 text-gray-600">{department.name}</td>
      ) : null}
      <td className="px-3 py-2">
        <input
          className="w-40 min-w-[8rem] rounded border border-gray-300 px-2 py-1 text-xs"
          value={lecturerName}
          onChange={(e) => setLecturerName(e.target.value)}
          placeholder="Họ tên GV"
        />
      </td>
      <td className="px-3 py-2">
        <input
          className="w-44 min-w-[9rem] rounded border border-gray-300 px-2 py-1 text-xs"
          value={lecturerEmail}
          onChange={(e) => setLecturerEmail(e.target.value)}
          placeholder="Email"
        />
      </td>
      <td className="px-3 py-2">
        <input
          className="w-32 min-w-[7rem] rounded border border-gray-300 px-2 py-1 text-xs"
          value={lecturerPhone}
          onChange={(e) => setLecturerPhone(e.target.value)}
          placeholder="SĐT"
        />
      </td>
      <td className="px-3 py-2 text-right">
        <button
          type="button"
          className="inline-flex items-center justify-center rounded-md bg-success-500 px-2 py-1 text-xs font-medium text-white shadow-theme-xs hover:bg-success-600"
          onClick={handleSave}
        >
          {savedFlash ? 'Đã lưu' : 'Lưu'}
        </button>
      </td>
    </tr>
  )
}
