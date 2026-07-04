import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from 'react'

export type ToastType = 'success' | 'error' | 'info'

type ToastItem = { id: string; message: string; type: ToastType }

type ToastContextValue = {
  push: (message: string, type?: ToastType) => void
}

const ToastContext = createContext<ToastContextValue | null>(null)

function toastId() {
  return `toast-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 6)}`
}

function toastStyles(type: ToastType) {
  switch (type) {
    case 'success':
      return 'border-success-200 bg-success-50 text-success-900 dark:border-success-900 dark:bg-success-950/50 dark:text-success-100'
    case 'error':
      return 'border-error-200 bg-error-50 text-error-900 dark:border-error-900 dark:bg-error-950/50 dark:text-error-100'
    default:
      return 'border-gray-200 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90'
  }
}

export function ToastProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<ToastItem[]>([])
  const timers = useRef(new Map<string, ReturnType<typeof setTimeout>>())

  const remove = useCallback((id: string) => {
    const t = timers.current.get(id)
    if (t) clearTimeout(t)
    timers.current.delete(id)
    setItems((list) => list.filter((i) => i.id !== id))
  }, [])

  const push = useCallback(
    (message: string, type: ToastType = 'info') => {
      const id = toastId()
      setItems((list) => [...list, { id, message, type }])
      const t = setTimeout(() => remove(id), 4200)
      timers.current.set(id, t)
    },
    [remove],
  )

  return (
    <ToastContext.Provider value={{ push }}>
      {children}
      <div
        className="pointer-events-none fixed right-4 top-16 z-[90] flex w-[min(100%-2rem,22rem)] flex-col gap-2"
        aria-live="polite"
        aria-relevant="additions text"
      >
        {items.map((t) => (
          <div
            key={t.id}
            role="status"
            className={`pointer-events-auto flex items-start justify-between gap-3 rounded-xl border px-3 py-2.5 text-sm shadow-theme-sm ${toastStyles(t.type)}`}
          >
            <span className="min-w-0 flex-1 leading-snug">{t.message}</span>
            <button
              type="button"
              className="shrink-0 rounded px-1.5 text-base leading-none text-gray-500 hover:bg-black/5 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white"
              onClick={() => remove(t.id)}
              aria-label="Đóng thông báo"
            >
              ×
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  )
}

export function useToast() {
  const ctx = useContext(ToastContext)
  if (!ctx) throw new Error('useToast must be used within ToastProvider')

  const success = useCallback(
    (message: string) => ctx.push(message, 'success'),
    [ctx.push],
  )
  const error = useCallback(
    (message: string) => ctx.push(message, 'error'),
    [ctx.push],
  )
  const info = useCallback(
    (message: string) => ctx.push(message, 'info'),
    [ctx.push],
  )

  return useMemo(
    () => ({
      toast: ctx.push,
      success,
      error,
      info,
    }),
    [ctx.push, success, error, info],
  )
}
