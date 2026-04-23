import { type ReactNode } from 'react'
import { Link, NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '@/shared/context/AuthContext'
import { SidebarProvider, useSidebar } from '@/shared/context/SidebarContext'
import { getBrandTitle, getNavItemsForRole } from '@/shared/layouts/navItems'
import type { UserRole } from '@/shared/types/auth'

function cn(...parts: (string | false | undefined)[]) {
  return parts.filter(Boolean).join(' ')
}

function IconNav() {
  return (
    <svg
      className="shrink-0"
      width="20"
      height="20"
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden
    >
      <path
        d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"
        stroke="currentColor"
        strokeWidth="1.5"
      />
    </svg>
  )
}

function ShellBackdrop() {
  const { isMobileOpen, toggleMobileSidebar } = useSidebar()
  if (!isMobileOpen) return null
  return (
    <div
      className="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
      aria-hidden
      onClick={toggleMobileSidebar}
    />
  )
}

function AppShellInner({ role }: { role: UserRole }) {
  const { user, logout } = useAuth()
  const {
    isExpanded,
    isMobileOpen,
    isHovered,
    toggleSidebar,
    toggleMobileSidebar,
    setIsHovered,
  } = useSidebar()
  const nav = getNavItemsForRole(role)
  const brand = getBrandTitle(role)
  const showLabels = isExpanded || isHovered || isMobileOpen

  const handleToggle = () => {
    if (window.innerWidth >= 1024) toggleSidebar()
    else toggleMobileSidebar()
  }

  return (
    <div className="min-h-screen bg-gray-50 font-outfit text-gray-800 dark:bg-gray-900 dark:text-white/90">
      <ShellBackdrop />
      <aside
        className={cn(
          'fixed left-0 top-0 z-50 flex h-screen flex-col border-r border-gray-200 bg-white transition-all duration-300 ease-in-out dark:border-gray-800 dark:bg-gray-900',
          isExpanded || isHovered || isMobileOpen ? 'w-[290px]' : 'w-[90px]',
          isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        )}
        onMouseEnter={() => !isExpanded && setIsHovered(true)}
        onMouseLeave={() => setIsHovered(false)}
        aria-label="Thanh điều hướng"
      >
        <div
          className={cn(
            'flex h-20 items-center border-b border-gray-200 px-5 dark:border-gray-800',
            !showLabels && 'lg:justify-center lg:px-0',
          )}
        >
          <Link
            to="/"
            className="flex items-center gap-2 font-semibold tracking-tight text-gray-900 no-underline dark:text-white"
          >
            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-500 text-sm font-bold text-white">
              H
            </span>
            {showLabels ? <span className="truncate text-lg">{brand}</span> : null}
          </Link>
        </div>
        <nav className="custom-scrollbar flex flex-1 flex-col gap-1 overflow-y-auto px-3 py-5">
          {nav.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={item.end}
              className={({ isActive }) =>
                cn(
                  'menu-item group',
                  isActive ? 'menu-item-active' : 'menu-item-inactive',
                  !showLabels && 'lg:justify-center',
                )
              }
            >
              {({ isActive }) => (
                <>
                  <span
                    className={cn(
                      'menu-item-icon-size',
                      isActive ? 'menu-item-icon-active' : 'menu-item-icon-inactive',
                    )}
                  >
                    <IconNav />
                  </span>
                  {showLabels ? (
                    <span className="truncate text-theme-sm font-medium">
                      {item.label}
                    </span>
                  ) : null}
                </>
              )}
            </NavLink>
          ))}
        </nav>
      </aside>

      <div
        className={cn(
          'flex min-h-screen flex-1 flex-col transition-all duration-300 ease-in-out',
          isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]',
        )}
      >
        <header className="sticky top-0 z-30 flex w-full flex-wrap items-center gap-2 border-b border-gray-200 bg-white px-3 py-3 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:px-6 lg:py-4">
          <button
            type="button"
            className="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 lg:h-11 lg:w-11"
            onClick={handleToggle}
            aria-label={isMobileOpen ? 'Đóng menu' : 'Mở menu'}
          >
            {isMobileOpen ? (
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden>
                <path
                  d="M6 6l12 12M18 6L6 18"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                />
              </svg>
            ) : (
              <svg width="18" height="14" viewBox="0 0 16 12" fill="none" aria-hidden>
                <path
                  fillRule="evenodd"
                  clipRule="evenodd"
                  d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z"
                  fill="currentColor"
                />
              </svg>
            )}
          </button>
          <div className="hidden min-w-0 flex-1 text-sm text-gray-500 dark:text-gray-400 lg:block">
            HUCE-ISRS — UI demo (TailAdmin theme)
          </div>
          <div className="ml-auto flex items-center gap-2 sm:gap-3">
            <span className="hidden max-w-[12rem] truncate text-sm text-gray-700 dark:text-gray-300 sm:inline">
              {user?.displayName}
            </span>
            <button
              type="button"
              className="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
              onClick={() => logout()}
            >
              Đăng xuất
            </button>
          </div>
        </header>

        <main className="flex-1 p-4 md:p-6">
          <div className="mx-auto max-w-(--breakpoint-2xl)">
            <Outlet />
          </div>
        </main>

        <footer className="border-t border-gray-200 bg-white px-4 py-3 text-center text-theme-xs text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
          HUCE-ISRS · Giao diện theo{' '}
          <span className="text-gray-700 dark:text-gray-300">TailAdmin</span> (free-react-tailwind-admin-dashboard)
        </footer>
      </div>
    </div>
  )
}

export function AppShellLayout({ role }: { role: UserRole }) {
  return (
    <SidebarProvider>
      <AppShellInner role={role} />
    </SidebarProvider>
  )
}

/** Layout đăng nhập — tông giống AuthPageLayout của TailAdmin. */
export function CenteredCardLayout({ children }: { children: ReactNode }) {
  return (
    <div className="relative z-1 min-h-screen bg-white font-outfit dark:bg-gray-900">
      <div className="flex min-h-screen flex-col lg:flex-row">
        <div className="flex flex-1 flex-col justify-center px-4 py-10 sm:px-6 lg:px-16 xl:px-24">
          <div className="mx-auto w-full max-w-md">{children}</div>
        </div>
        <div className="relative hidden min-h-screen flex-1 flex-col items-center justify-center overflow-hidden bg-brand-950 lg:flex">
          <div
            className="pointer-events-none absolute inset-0 opacity-30"
            style={{
              backgroundImage: `radial-gradient(circle at 20% 20%, rgba(255,255,255,0.15) 0, transparent 40%),
                radial-gradient(circle at 80% 60%, rgba(117,146,255,0.35) 0, transparent 45%)`,
            }}
          />
          <div className="relative z-1 max-w-sm px-8 text-center">
            <p className="text-2xl font-semibold text-white">HUCE-ISRS</p>
          </div>
        </div>
      </div>
    </div>
  )
}
