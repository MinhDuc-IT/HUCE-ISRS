import { useEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/shared/utils/apiClient'
import { useToast } from '@/shared/context/ToastContext'
import type { ApiRemedialTerm } from '@/shared/types/api'
import { RemedialTermStatusBadge } from '@/modules/admin/components/RemedialTermStatusBadge'
import { formatRemedialTermStatus, type RemedialTermStatus } from '@/shared/constants/term'

type RemedialTermStatusCode = 0 | 1 | 2 | 3 | 4

const statusNameByCode: Record<RemedialTermStatusCode, string> = {
  0: 'DRAFT',
  1: 'REGISTRATION_OPEN',
  2: 'ACTIVE',
  3: 'COMPLETED',
  4: 'CANCELLED',
}

const nextStatusByCurrent: Partial<Record<RemedialTermStatusCode, number[]>> = {
  0: [1, 4],
  1: [4],
  2: [3, 4],
}

type MenuPosition = { top: number; left: number }

export function CohortListPage() {
  const [terms, setTerms] = useState<ApiRemedialTerm[]>([])
  const [loading, setLoading] = useState(true)
  const [updatingId, setUpdatingId] = useState<number | null>(null)
  const [openMenuId, setOpenMenuId] = useState<number | null>(null)
  const [menuPosition, setMenuPosition] = useState<MenuPosition | null>(null)
  const menuButtonRefs = useRef<Record<number, HTMLButtonElement | null>>({})
  const menuPanelRef = useRef<HTMLDivElement | null>(null)
  const { success, error: showError } = useToast()

  useEffect(() => {
    void fetchTerms()
  }, [])

  useEffect(() => {
    function handleDocumentClick(event: MouseEvent) {
      const target = event.target as Node
      const activeButton = openMenuId ? menuButtonRefs.current[openMenuId] : null
      const activePanel = menuPanelRef.current
      if (
        activeButton &&
        activePanel &&
        !activeButton.contains(target) &&
        !activePanel.contains(target)
      ) {
        setOpenMenuId(null)
        setMenuPosition(null)
      }
    }

    function handleEscape(event: KeyboardEvent) {
      if (event.key === 'Escape') {
        setOpenMenuId(null)
        setMenuPosition(null)
      }
    }

    document.addEventListener('mousedown', handleDocumentClick)
    document.addEventListener('keydown', handleEscape)
    return () => {
      document.removeEventListener('mousedown', handleDocumentClick)
      document.removeEventListener('keydown', handleEscape)
    }
  }, [openMenuId])

  async function fetchTerms() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiRemedialTerm[] }>('/admin/remedial-terms')
      setTerms(res.data || [])
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      showError('Lỗi tải danh sách đợt phụ đạo: ' + msg)
    } finally {
      setLoading(false)
    }
  }

  async function handleDelete(id: number, name: string) {
    if (!window.confirm(`Xóa đợt "${name}"? Thao tác không thể hoàn tác.`)) return
    try {
      await apiFetch(`/admin/remedial-terms/${id}`, { method: 'DELETE' })
      success('Đã xóa đợt phụ đạo.')
      await fetchTerms()
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Không thể xóa đợt phụ đạo.'
      showError(msg)
    }
  }

  async function handleStatusUpdate(id: number, nextStatus: RemedialTermStatusCode) {
    try {
      setUpdatingId(id)
      await apiFetch(`/admin/remedial-terms/${id}/status`, {
        method: 'PATCH',
        data: { status: nextStatus },
      })
      success('Đã cập nhật trạng thái đợt phụ đạo.')
      setOpenMenuId(null)
      setMenuPosition(null)
      await fetchTerms()
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Không thể cập nhật trạng thái.'
      showError(msg)
    } finally {
      setUpdatingId(null)
    }
  }

  function formatDate(d: string | null | undefined) {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('vi-VN')
  }

  function openMenu(termId: number) {
    const button = menuButtonRefs.current[termId]
    if (!button) return

    const rect = button.getBoundingClientRect()
    const menuWidth = 208
    setOpenMenuId(termId)
    setMenuPosition({
      top: rect.bottom + window.scrollY + 4,
      left: Math.max(12, rect.right + window.scrollX - menuWidth),
    })
  }

  function closeMenu() {
    setOpenMenuId(null)
    setMenuPosition(null)
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-lg font-semibold text-gray-800">Đợt phụ đạo</h1>
        <Link
          to="/admin/cohorts/new"
          className="inline-flex items-center rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600"
        >
          + Thêm đợt
        </Link>
      </div>

      <div className="h-[calc(100vh-375px)] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="h-full overflow-y-auto overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 font-semibold">#</th>
                <th className="px-4 py-3 font-semibold">Tên đợt</th>
                <th className="px-4 py-3 font-semibold">Ngày bắt đầu</th>
                <th className="px-4 py-3 font-semibold">Ngày kết thúc</th>
                <th className="px-4 py-3 font-semibold">Đăng ký từ</th>
                <th className="px-4 py-3 font-semibold">Đăng ký đến</th>
                <th className="px-4 py-3 font-semibold">Trạng thái</th>
                <th className="px-4 py-3 text-right font-semibold">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan={8} className="px-4 py-8 text-center text-gray-500">
                    Đang tải...
                  </td>
                </tr>
              ) : terms.length === 0 ? (
                <tr>
                  <td colSpan={8} className="px-4 py-8 text-center text-gray-500">
                    Chưa có đợt phụ đạo nào.
                  </td>
                </tr>
              ) : (
                terms.map((term, index) => {
                  const currentStatus = (term.status ?? 0) as RemedialTermStatusCode
                  const isMenuOpen = openMenuId === term.id


                  return (
                    <tr key={term.id} className="hover:bg-gray-50/80">
                      <td className="px-4 py-3 text-gray-500">{index + 1}</td>
                      <td className="px-4 py-3 font-medium text-gray-800">{term.name}</td>
                      <td className="px-4 py-3 text-gray-600">{formatDate(term.start_date)}</td>
                      <td className="px-4 py-3 text-gray-600">{formatDate(term.end_date)}</td>
                      <td className="px-4 py-3 text-gray-600">{formatDate(term.registration_start)}</td>
                      <td className="px-4 py-3 text-gray-600">{formatDate(term.registration_end)}</td>
                      <td className="px-4 py-3 text-gray-700">
                        {term.status_logic != null ? (
                          <RemedialTermStatusBadge status={term.status_logic as RemedialTermStatus} label={term.status_logic_name} />
                        ) : (
                          term.status_name ?? formatRemedialTermStatus(term.status == null ? undefined : statusNameByCode[currentStatus])
                        )}
                      </td>
                      <td className="px-4 py-3 text-right">
                        <button
                          ref={(node) => {
                            menuButtonRefs.current[term.id] = node
                          }}
                          type="button"
                          className="inline-flex size-8 items-center justify-center rounded-full text-lg leading-none text-gray-600 hover:bg-gray-100"
                          onClick={() => {
                            if (isMenuOpen) {
                              closeMenu()
                            } else {
                              openMenu(term.id)
                            }
                          }}
                          aria-label="Mở menu thao tác"
                        >
                          ...
                        </button>
                      </td>
                    </tr>
                  )
                })
              )}
            </tbody>
          </table>
        </div>
      </div>

      {openMenuId !== null && menuPosition && typeof document !== 'undefined'
        ? createPortal(
            <div
              className="fixed inset-0 z-[99999]"
              onClick={closeMenu}
              onKeyDown={(event) => {
                if (event.key === 'Escape') closeMenu()
              }}
            >
              <div
                ref={menuPanelRef}
                className="absolute w-[208px] rounded-xl border border-gray-200 bg-white px-2 py-2 shadow-[0_8px_30px_rgba(16,24,40,0.12)]"
                style={{ top: `${menuPosition.top}px`, left: `${menuPosition.left}px` }}
                onMouseDown={(event) => event.stopPropagation()}
                onClick={(event) => event.stopPropagation()}
              >
                {(() => {
                  const term = terms.find((item) => item.id === openMenuId)
                  if (!term) return null
                  const currentStatus = (term.status ?? 0) as RemedialTermStatusCode
                  const actions = nextStatusByCurrent[currentStatus] ?? []

                  return (
                    <>
                      <Link
                        to={`/admin/cohorts/${term.id}/edit`}
                        className="flex items-center rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        onClick={closeMenu}
                      >
                        Sửa
                      </Link>

                      <button
                        type="button"
                        className="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                        onClick={() => {
                          closeMenu()
                          void handleDelete(term.id, term.name)
                        }}
                      >
                        Xóa
                      </button>

                      {actions.map((action) => (
                        <button
                          key={action}
                          type="button"
                          className="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 disabled:opacity-60"
                          disabled={updatingId === term.id}
                          onClick={() => void handleStatusUpdate(term.id, action)}
                        >
                          Chuyển sang {formatRemedialTermStatus(statusNameByCode[action])}
                        </button>
                      ))}
                    </>
                  )
                })()}
              </div>
            </div>,
            document.body
          )
        : null}
    </div>
  )
}
