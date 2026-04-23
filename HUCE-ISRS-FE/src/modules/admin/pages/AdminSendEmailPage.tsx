import { useState, type FormEvent } from 'react'
import { useDemoData } from '@/shared/context/DemoDataContext'

export function AdminSendEmailPage() {
  const { state, sendDepartmentEmail } = useDemoData()
  const [departmentId, setDepartmentId] = useState(state.departments[0]?.id ?? '')
  const [toEmail, setToEmail] = useState(
    () => state.departments[0]?.headEmail ?? '',
  )
  const [subject, setSubject] = useState('Thông báo phụ đạo — danh sách môn/SV')
  const [body, setBody] = useState(
    'Kính gửi Trưởng bộ môn,\n\nĐính kèm nội dung danh sách môn phụ đạo và sinh viên (demo UI).\n',
  )
  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)

  function onDeptChange(id: string) {
    setDepartmentId(id)
    const d = state.departments.find((x) => x.id === id)
    if (d) setToEmail(d.headEmail)
  }

  function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setMessage(null)
    setError(null)
    const res = sendDepartmentEmail({
      departmentId,
      toEmail: toEmail.trim(),
      subject,
      body,
    })
    if (!res.ok) {
      setError('Email người nhận không hợp lệ.')
      return
    }
    setMessage('Đã gửi (mock — chỉ ghi log trong session).')
  }

  return (
    <div className="mx-auto max-w-2xl space-y-4">
      <div>
        <h1 className="text-lg font-semibold text-gray-800">
          Gửi email về bộ môn
        </h1>
      </div>

      <form
        onSubmit={handleSubmit}
        className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm"
      >
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Bộ môn
          </label>
          <select
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={departmentId}
            onChange={(e) => onDeptChange(e.target.value)}
          >
            {state.departments.map((d) => (
              <option key={d.id} value={d.id}>
                {d.name}
              </option>
            ))}
          </select>
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Email nhận
          </label>
          <input
            type="email"
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={toEmail}
            onChange={(e) => setToEmail(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Tiêu đề
          </label>
          <input
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={subject}
            onChange={(e) => setSubject(e.target.value)}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-700">
            Nội dung
          </label>
          <textarea
            rows={8}
            className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"
            value={body}
            onChange={(e) => setBody(e.target.value)}
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
          Gửi email
        </button>
      </form>

      {state.emailLogs.length > 0 ? (
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
          <h2 className="text-sm font-semibold text-gray-800">Log gửi gần đây</h2>
          <ul className="mt-2 max-h-48 space-y-2 overflow-y-auto text-xs text-gray-600">
            {state.emailLogs.slice(0, 8).map((log) => (
              <li key={log.id} className="border-b border-gray-100 pb-2">
                <span className="font-medium text-gray-700">{log.subject}</span>
                <br />
                {new Date(log.sentAt).toLocaleString('vi-VN')} —{' '}
                {log.ok ? 'OK' : 'Lỗi'}
              </li>
            ))}
          </ul>
        </div>
      ) : null}
    </div>
  )
}
