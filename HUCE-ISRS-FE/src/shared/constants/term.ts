export type RemedialTermStatus = 0 | 1 | 2 | 3 | 4 | 10 | 11 | 12 | 13 | 14 | 30 | 40

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
