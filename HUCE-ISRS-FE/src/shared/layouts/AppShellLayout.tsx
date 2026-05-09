import { type ReactNode } from 'react'
import { NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { getNavItemsForRole } from '@/shared/layouts/navItems'
import type { UserRole } from '@/shared/types/auth'
import { useDemoData } from '@/shared/context/DemoDataContext'

function cn(...parts: (string | false | undefined)[]) {
  return parts.filter(Boolean).join(' ')
}

function AppShellInner({ role }: { role: UserRole }) {
  const { user, logout } = useAuth()
  const nav = getNavItemsForRole(role)
  
  // Lấy thông tin đợt mở đăng ký (chỉ để demo Sub-header)
  const { getOpenCohortsForRegistration } = useDemoData()
  const openCohorts = getOpenCohortsForRegistration()
  const currentCohortMsg = openCohorts.length > 0 
    ? `Đợt đăng ký: ${openCohorts[0].name}`
    : 'Hiện chưa có đợt đăng ký phụ đạo nào đang mở'

  return (
    <div className="min-h-screen bg-gray-50 font-sans text-gray-800 flex flex-col">
      {/* 1. Header Chính (Dark Blue) */}
      <header className="bg-[#0f3460] text-white py-3 px-4 shadow-md">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          
          {/* Logo & Tên Trường */}
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-white flex items-center justify-center rounded text-[#0f3460] font-bold text-xs text-center leading-none">
              HUCE<br/>LOGO
            </div>
            <div>
              <h1 className="text-xl md:text-2xl font-bold uppercase tracking-wide">Trường Đại Học Xây Dựng</h1>
              <p className="text-xs md:text-sm text-gray-300">National University of Civil Engineering</p>
            </div>
          </div>

          {/* Tên Hệ Thống & Info User */}
          <div className="flex flex-col items-end gap-1">
            <div className="text-xs md:text-sm text-gray-200">
              {user?.displayName} |{' '}
              <button onClick={() => logout()} className="hover:text-white hover:underline transition-colors">
                Đăng xuất
              </button>
            </div>
            <div className="text-right mt-1">
              <h2 className="text-lg md:text-xl font-bold text-[#fbc02d] uppercase tracking-wide drop-shadow-sm">
                Hệ Thống Đăng Ký Học Phụ Đạo
              </h2>
            </div>
          </div>
        </div>
      </header>

      {/* 2. Sub-header (Thanh thông báo xanh nhạt) */}
      <div className="bg-[#1976d2] text-white px-4 py-2 text-sm shadow-sm">
        <div className="max-w-7xl mx-auto">
          {currentCohortMsg}
        </div>
      </div>

      {/* 3. Navigation Bar (Ngang) */}
      {nav.length > 0 && (
        <nav className="bg-[#547294] shadow-sm sticky top-0 z-40">
          <div className="max-w-7xl mx-auto flex flex-wrap items-center">
            {nav.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.end}
                className={({ isActive }) =>
                  cn(
                    'px-4 py-3 text-sm font-medium transition-colors border-r border-[#6985a5]',
                    isActive
                      ? 'bg-[#3b5998] text-white'
                      : 'text-gray-100 hover:bg-[#46658b] hover:text-white'
                  )
                }
              >
                {item.label}
              </NavLink>
            ))}
          </div>
        </nav>
      )}

      {/* 4. Main Content */}
      <main className="flex-1 w-full max-w-7xl mx-auto p-4 md:p-6 mt-4">
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <Outlet />
        </div>
      </main>

      {/* 5. Footer */}
      <footer className="mt-auto py-4 text-center text-xs text-gray-500 border-t border-gray-200 bg-white">
        &copy; 2026 HUCE-ISRS. Giao diện được nâng cấp theo chuẩn thiết kế mới.
      </footer>
    </div>
  )
}

export function AppShellLayout({ role }: { role: UserRole }) {
  return <AppShellInner role={role} />
}

/** Layout đăng nhập */
export function CenteredCardLayout({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-cover bg-center" style={{ backgroundImage: 'linear-gradient(to bottom right, #0f3460, #1976d2)' }}>
      <div className="sm:mx-auto sm:w-full sm:max-w-md">
        <div className="text-center">
          <h2 className="mt-6 text-center text-3xl font-extrabold text-white">
            HỆ THỐNG ĐĂNG KÝ HỌC PHỤ ĐẠO
          </h2>
          <p className="mt-2 text-center text-sm text-gray-200">
            Trường Đại Học Xây Dựng
          </p>
        </div>
        <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
          <div className="bg-white py-8 px-4 shadow-xl sm:rounded-lg sm:px-10 border border-gray-100">
            {children}
          </div>
        </div>
      </div>
    </div>
  )
}
