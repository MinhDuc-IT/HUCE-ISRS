import { BrowserRouter } from 'react-router-dom'
import { AuthProvider } from '@/shared/context/AuthContext'
import { ConfirmProvider } from '@/shared/context/ConfirmContext'
import { ToastProvider } from '@/shared/context/ToastContext'
import { ErrorBoundary } from '@/shared/components/ErrorBoundary'
import { AppRoutes } from '@/app/router'

export default function App() {
  return (
    <BrowserRouter>
      <ToastProvider>
        <ConfirmProvider>
          <AuthProvider>
            <ErrorBoundary>
              <AppRoutes />
            </ErrorBoundary>
          </AuthProvider>
        </ConfirmProvider>
      </ToastProvider>
    </BrowserRouter>
  )
}
