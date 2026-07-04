import type { RemedialTermStatus } from '@/shared/constants/term'

const styles: Record<RemedialTermStatus, string> = {
  0: 'bg-gray-100 text-gray-700 border-gray-200',
  1: 'bg-sky-50 text-sky-800 border-sky-200',
  2: 'bg-emerald-50 text-emerald-800 border-emerald-200',
  3: 'bg-amber-50 text-amber-900 border-amber-200',
  4: 'bg-rose-50 text-rose-800 border-rose-200',
  
  // Logic statuses
  10: 'bg-gray-50 text-gray-600 border-gray-200', // Sắp mở đăng ký
  11: 'bg-sky-50 text-sky-800 border-sky-200', // Đang mở đăng ký
  12: 'bg-indigo-50 text-indigo-800 border-indigo-200', // Đã đóng đăng ký (chờ học)
  13: 'bg-emerald-50 text-emerald-800 border-emerald-200', // Đang học
  14: 'bg-orange-50 text-orange-800 border-orange-200', // Chờ hoàn thành
  30: 'bg-amber-50 text-amber-900 border-amber-200', // Hoàn thành
  40: 'bg-rose-50 text-rose-800 border-rose-200', // Đã hủy
}

const labels: Record<RemedialTermStatus, string> = {
  0: 'Nháp',
  1: 'Mở đăng ký',
  2: 'Đang học',
  3: 'Hoàn thành',
  4: 'Đã hủy',
  10: 'Sắp mở đăng ký',
  11: 'Đang mở đăng ký',
  12: 'Đã đóng đăng ký',
  13: 'Đang học',
  14: 'Chờ hoàn thành',
  30: 'Đã hoàn thành',
  40: 'Đã hủy',
}

export function RemedialTermStatusBadge({ status, label }: { status: RemedialTermStatus; label?: string }) {
  return (
    <span className={`inline-block rounded border px-2 py-0.5 text-xs font-medium ${styles[status]}`}>
      {label || labels[status]}
    </span>
  )
}
