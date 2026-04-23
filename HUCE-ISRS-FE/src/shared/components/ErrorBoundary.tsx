import { Component, type ErrorInfo, type ReactNode } from 'react'

type Props = { children: ReactNode }

type State = {
  error: Error | null
}

export class ErrorBoundary extends Component<Props, State> {
  state: State = { error: null }

  static getDerivedStateFromError(error: Error): State {
    return { error }
  }

  override componentDidCatch(error: Error, info: ErrorInfo) {
    console.error('[ErrorBoundary]', error, info.componentStack)
  }

  override render() {
    if (this.state.error) {
      return (
        <div className="flex min-h-screen flex-col items-center justify-center gap-4 bg-gray-50 p-6 text-center dark:bg-gray-900">
          <h1 className="text-lg font-semibold text-gray-800">
            Đã xảy ra lỗi giao diện
          </h1>
          <p className="max-w-md text-sm text-gray-600">
            Vui lòng tải lại trang. Nếu lỗi lặp lại, thử xóa dữ liệu session của
            trình duyệt cho domain này (mock lưu trong sessionStorage).
          </p>
          <pre className="max-h-32 max-w-full overflow-auto rounded-xl border border-error-200 bg-error-50 p-3 text-left text-xs text-error-800 dark:border-error-900 dark:bg-error-950/40 dark:text-error-200">
            {this.state.error.message}
          </pre>
          <button
            type="button"
            className="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600"
            onClick={() => window.location.reload()}
          >
            Tải lại trang
          </button>
        </div>
      )
    }
    return this.props.children
  }
}
