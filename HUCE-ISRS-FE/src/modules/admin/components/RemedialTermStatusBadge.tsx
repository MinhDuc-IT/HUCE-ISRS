type RemedialTermStatus = 0 | 1 | 2 | 3 | 4

const styles: Record<RemedialTermStatus, string> = {
  0: 'bg-gray-100 text-gray-700 border-gray-200',
  1: 'bg-sky-50 text-sky-800 border-sky-200',
  2: 'bg-emerald-50 text-emerald-800 border-emerald-200',
  3: 'bg-amber-50 text-amber-900 border-amber-200',
  4: 'bg-rose-50 text-rose-800 border-rose-200',
}

const labels: Record<RemedialTermStatus, string> = {
  0: 'Nháp',
  1: 'Mở đăng ký',
  2: 'Đang học',
  3: 'Hoàn thành',
  4: 'Đã hủy',
}

export function RemedialTermStatusBadge({ status }: { status: RemedialTermStatus }) {
  return (
    <span className={`inline-block rounded border px-2 py-0.5 text-xs font-medium ${styles[status]}`}>
      {labels[status]}
    </span>
  )
}

export function formatRemedialTermStatus(status?: string | null): string {
  const labels: Record<string, string> = {
    DRAFT: 'Nháp',
    REGISTRATION_OPEN: 'Mở đăng ký',
    ACTIVE: 'Đang học',
    COMPLETED: 'Hoàn thành',
    CANCELLED: 'Đã hủy',
  }

  return status ? labels[status] ?? status : 'Nháp'
}
