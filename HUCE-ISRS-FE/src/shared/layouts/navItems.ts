import type { UserRole } from '@/shared/types/auth'

export type NavItem = {
  to: string
  label: string
  end?: boolean
}

const adminNav: NavItem[] = [
  { to: '/admin', label: 'Trang chủ', end: true },
  { to: '/admin/cohorts', label: 'Đợt phụ đạo' },
  { to: '/admin/users', label: 'Người dùng' },
  { to: '/admin/departments', label: 'Bộ môn' },
  { to: '/admin/registrations', label: 'Tra cứu đăng ký' },
  { to: '/admin/email-department', label: 'Gửi email BM' },
  { to: '/admin/settings', label: 'Cài đặt' },
  { to: '/admin/statistics', label: 'Thống kê đợt' },
  { to: '/admin/teaching-payments', label: 'Thanh toán tiền phụ đạo' },
]

const departmentNav: NavItem[] = [
  { to: '/department', label: 'Trang chủ', end: true },
  { to: '/department/profile', label: 'Thông tin bộ môn' },
  { to: '/department/assignments', label: 'Phân công GV' },
]

const studentNav: NavItem[] = [
  { to: '/student', label: 'Trang chủ', end: true },
  { to: '/student/register', label: 'Đăng ký phụ đạo' },
  { to: '/student/registrations', label: 'Môn đã đăng ký' },
  { to: '/student/instructors', label: 'Giảng viên PD' },
]

export function getNavItemsForRole(role: UserRole): NavItem[] {
  switch (role) {
    case 'admin':
      return adminNav
    case 'department':
      return departmentNav
    case 'student':
      return studentNav
  }
}

export function getBrandTitle(role: UserRole): string {
  switch (role) {
    case 'admin':
      return 'ISRS Admin'
    case 'department':
      return 'ISRS Bộ môn'
    case 'student':
      return 'ISRS Sinh viên'
  }
}
