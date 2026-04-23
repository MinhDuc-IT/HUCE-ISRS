import {
  createContext,
  useCallback,
  useContext,
  useRef,
  useState,
  type ReactNode,
} from 'react'

export type ConfirmOptions = {
  title: string
  message: string
  confirmLabel?: string
  cancelLabel?: string
  /** Nút xác nhận màu đỏ (xóa / reset). */
  variant?: 'danger' | 'default'
}

type ConfirmContextValue = {
  confirm: (opts: ConfirmOptions) => Promise<boolean>
}

const ConfirmContext = createContext<ConfirmContextValue | null>(null)

export function ConfirmProvider({ children }: { children: ReactNode }) {
  const [dialog, setDialog] = useState<ConfirmOptions | null>(null)
  const resolveRef = useRef<((value: boolean) => void) | null>(null)
  const busyRef = useRef(false)

  const confirm = useCallback((opts: ConfirmOptions) => {
    if (busyRef.current) {
      return Promise.resolve(false)
    }
    busyRef.current = true
    return new Promise<boolean>((resolve) => {
      resolveRef.current = resolve
      setDialog(opts)
    })
  }, [])

  const finish = useCallback((value: boolean) => {
    resolveRef.current?.(value)
    resolveRef.current = null
    busyRef.current = false
    setDialog(null)
  }, [])

  return (
    <ConfirmContext.Provider value={{ confirm }}>
      {children}
      {dialog ? (
        <div
          className="fixed inset-0 z-[100] flex items-center justify-center bg-black/45 p-4"
          role="presentation"
          onMouseDown={(e) => {
            if (e.target === e.currentTarget) finish(false)
          }}
        >
          <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="confirm-title"
            className="w-full max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"
          >
            <h2
              id="confirm-title"
              className="text-base font-semibold text-gray-900 dark:text-white"
            >
              {dialog.title}
            </h2>
            <p className="mt-2 whitespace-pre-wrap text-sm text-gray-600 dark:text-gray-400">
              {dialog.message}
            </p>
            <div className="mt-5 flex flex-wrap justify-end gap-2">
              <button
                type="button"
                className="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                onClick={() => finish(false)}
              >
                {dialog.cancelLabel ?? 'Hủy'}
              </button>
              <button
                type="button"
                className={
                  dialog.variant === 'danger'
                    ? 'rounded-lg bg-error-600 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-error-700'
                    : 'rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600'
                }
                onClick={() => finish(true)}
              >
                {dialog.confirmLabel ?? 'Đồng ý'}
              </button>
            </div>
          </div>
        </div>
      ) : null}
    </ConfirmContext.Provider>
  )
}

export function useConfirm() {
  const ctx = useContext(ConfirmContext)
  if (!ctx) throw new Error('useConfirm must be used within ConfirmProvider')
  return ctx
}
