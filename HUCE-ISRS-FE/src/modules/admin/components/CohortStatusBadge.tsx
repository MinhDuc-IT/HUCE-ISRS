import type { CohortStatus } from '@/shared/types/remedial'

const styles: Record<CohortStatus, string> = {
  draft: 'bg-gray-100 text-gray-700 border-gray-200',
  open: 'bg-emerald-50 text-emerald-800 border-emerald-200',
  closed: 'bg-amber-50 text-amber-900 border-amber-200',
}

const labels: Record<CohortStatus, string> = {
  draft: 'Nháp',
  open: 'Đang mở',
  closed: 'Đã đóng',
}

export function CohortStatusBadge({ status }: { status: CohortStatus }) {
  return (
    <span
      className={`inline-block rounded border px-2 py-0.5 text-xs font-medium ${styles[status]}`}
    >
      {labels[status]}
    </span>
  )
}
