import { LecturerAssignmentTable } from '@/shared/components/LecturerAssignmentTable'

export function AdminAssignmentsPage() {
  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-lg font-semibold text-gray-800">
          Phân công giảng viên phụ đạo
        </h1>
      </div>
      <LecturerAssignmentTable departmentIdFilter={null} />
    </div>
  )
}
