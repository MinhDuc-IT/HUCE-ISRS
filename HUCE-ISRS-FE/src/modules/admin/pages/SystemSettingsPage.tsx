import { useState, useEffect, type FormEvent } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'

interface ApiSetting {
  id: string
  key: string
  value: string
  description?: string
}

export function SystemSettingsPage() {
  const [formData, setFormData] = useState({
    senderEmail: '',
    senderPassword: '',
    adminEmail: '',
    weeksFromRegistration: '',
    wsLogin: '',
    wsStudentInfo: '',
    wsHost: ''
  })

  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)
  const [isSubmitting, setIsSubmitting] = useState(false)

  useEffect(() => {
    fetchSettings()
  }, [])

  async function fetchSettings() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiSetting[] }>('/admin/settings')
      const settings = res.data || []
      
      // Map API settings (key-value) to our formData
      const newFormData = { ...formData }
      settings.forEach(s => {
        if (s.key === 'sender_email') newFormData.senderEmail = s.value
        if (s.key === 'sender_password') newFormData.senderPassword = s.value
        if (s.key === 'admin_email') newFormData.adminEmail = s.value
        if (s.key === 'weeks_from_registration') newFormData.weeksFromRegistration = s.value
        if (s.key === 'ws_login') newFormData.wsLogin = s.value
        if (s.key === 'ws_student_info') newFormData.wsStudentInfo = s.value
        if (s.key === 'ws_host') newFormData.wsHost = s.value
      })
      setFormData(newFormData)
    } catch (err: any) {
      setError('Lỗi khi tải cấu hình: ' + err.message)
    } finally {
      setLoading(false)
    }
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setMessage(null)
    setError(null)
    
    try {
      setIsSubmitting(true)
      
      // Chuyển đổi formData sang mảng key-value cho Backend
      const payload = {
        settings: [
          { key: 'sender_email', value: formData.senderEmail },
          { key: 'sender_password', value: formData.senderPassword },
          { key: 'admin_email', value: formData.adminEmail },
          { key: 'weeks_from_registration', value: formData.weeksFromRegistration },
          { key: 'ws_login', value: formData.wsLogin },
          { key: 'ws_student_info', value: formData.wsStudentInfo },
          { key: 'ws_host', value: formData.wsHost },
        ]
      }

      await apiFetch('/admin/settings', {
        method: 'POST',
        data: payload
      })

      setMessage('Đã lưu cấu hình hệ thống thành công.')
      setTimeout(() => setMessage(null), 3000)
    } catch (err: any) {
      setError('Lỗi khi lưu cấu hình: ' + err.message)
    } finally {
      setIsSubmitting(false)
    }
  }

  function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
    setFormData({ ...formData, [e.target.name]: e.target.value })
  }

  return (
    <div className="mx-auto max-w-4xl pt-4">
      {error && (
        <div className="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}
      <div className="bg-white rounded border border-gray-200 shadow-sm relative">
        {loading && (
          <div className="absolute inset-0 bg-white/60 z-10 flex items-center justify-center backdrop-blur-[1px]">
            <span className="text-gray-500 font-medium">Đang tải cấu hình...</span>
          </div>
        )}
        <div className="bg-[#1976d2] text-white px-4 py-2.5 rounded-t text-center font-medium text-sm">
          Cấu hình hệ thống
        </div>
        
        <form onSubmit={handleSubmit} className="p-6">
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-bold text-gray-800 mb-1">
                Email dùng để gửi email:
              </label>
              <input
                type="email"
                name="senderEmail"
                value={formData.senderEmail}
                onChange={handleChange}
                className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-800 mb-1">
                Password:
              </label>
              <input
                type="password"
                name="senderPassword"
                value={formData.senderPassword}
                onChange={handleChange}
                className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-800 mb-1">
                Email đơn vị quản lý:
              </label>
              <input
                type="email"
                name="adminEmail"
                value={formData.adminEmail}
                onChange={handleChange}
                className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-800 mb-1">
                Số tuần tính từ tuần đăng ký:
              </label>
              <input
                type="text"
                name="weeksFromRegistration"
                value={formData.weeksFromRegistration}
                onChange={handleChange}
                className="w-24 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-800 mb-1">
                Webservice đăng nhập:
              </label>
              <input
                type="text"
                name="wsLogin"
                value={formData.wsLogin}
                onChange={handleChange}
                className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-800 mb-1">
                Webservice lấy thông tin sinh viên:
              </label>
              <input
                type="text"
                name="wsStudentInfo"
                value={formData.wsStudentInfo}
                onChange={handleChange}
                className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-800 mb-1">
                Host webservice học phụ đạo:
              </label>
              <input
                type="text"
                name="wsHost"
                value={formData.wsHost}
                onChange={handleChange}
                className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
              />
            </div>
          </div>

          <div className="mt-8 flex justify-end items-center gap-4">
            {message && <span className="text-emerald-600 text-sm font-medium">{message}</span>}
            <button
              type="submit"
              disabled={isSubmitting || loading}
              className="bg-[#1976d2] hover:bg-[#1565c0] text-white px-6 py-2 rounded text-sm font-medium shadow-sm transition-colors disabled:opacity-70"
            >
              {isSubmitting ? 'Đang lưu...' : 'Lưu'}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}
