import { useState, type FormEvent } from 'react'
import { useDemoData } from '@/shared/context/DemoDataContext'

export function SystemSettingsPage() {
  const { state, updateSettings } = useDemoData()
  const { settings } = state
  const [schoolName, setSchoolName] = useState(settings.schoolName)
  const [supportEmail, setSupportEmail] = useState(settings.supportEmail)
  const [feePerRegistration, setFeePerRegistration] = useState(
    String(settings.feePerRegistration),
  )
  const [vatPercent, setVatPercent] = useState(String(settings.vatPercent))
  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)

  function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setMessage(null)
    setError(null)
    const fee = Number(feePerRegistration.replace(/\s/g, '').replace(/,/g, ''))
    const vat = Number(vatPercent.replace(',', '.'))
    if (!schoolName.trim() || !supportEmail.trim()) {
      setError('Nhập đủ tên trường và email hỗ trợ.')
      return
    }
    if (!Number.isFinite(fee) || fee < 0) {
      setError('Đơn giá không hợp lệ.')
      return
    }
    if (!Number.isFinite(vat) || vat < 0 || vat > 100) {
      setError('VAT % phải từ 0 đến 100.')
      return
    }
    updateSettings({
      schoolName: schoolName.trim(),
      supportEmail: supportEmail.trim(),
      feePerRegistration: Math.round(fee),
      vatPercent: Math.round(vat * 10) / 10,
    })
    setMessage('Đã lưu cấu hình (mock, áp dụng cho thanh toán & thống kê).')
  }

  return (
    <div className="mx-auto max-w-xl space-y-4">
      <div>
        <h1 className="text-lg font-semibold text-gray-800">Cài đặt hệ thống</h1>
      </div>

      <form
        onSubmit={handleSubmit}
        className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm"
      >
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Tên trường / đơn vị
          </label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={schoolName}
            onChange={(e) => setSchoolName(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Email hỗ trợ hệ thống
          </label>
          <input
            type="email"
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={supportEmail}
            onChange={(e) => setSupportEmail(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Đơn giá phụ đạo / 1 lượt đăng ký (VNĐ)
          </label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={feePerRegistration}
            onChange={(e) => setFeePerRegistration(e.target.value)}
            inputMode="numeric"
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            VAT (%)
          </label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={vatPercent}
            onChange={(e) => setVatPercent(e.target.value)}
            inputMode="decimal"
          />
        </div>

        {error ? (
          <p className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {error}
          </p>
        ) : null}
        {message ? (
          <p className="rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
            {message}
          </p>
        ) : null}

        <button
          type="submit"
          className="rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600"
        >
          Lưu cấu hình
        </button>
      </form>
    </div>
  )
}
