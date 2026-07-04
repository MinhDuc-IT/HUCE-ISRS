import { useState, useEffect, type FormEvent } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'

interface ApiSetting {
  key: string
  value: string
  description?: string
}

export function SystemSettingsPage() {
  const [settings, setSettings] = useState<ApiSetting[]>([])
  const [newSetting, setNewSetting] = useState<ApiSetting>({
    key: '',
    value: '',
    description: ''
  })
  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)
  const [savingKey, setSavingKey] = useState<string | null>(null)
  const [deletingKey, setDeletingKey] = useState<string | null>(null)
  const [isCreating, setIsCreating] = useState(false)

  async function fetchSettings() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiSetting[] }>('/admin/system-configurations')
      const list = res.data || []
      setSettings(list.sort((a, b) => a.key.localeCompare(b.key)))
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : String(err)
      setError('Lỗi khi tải cấu hình: ' + msg)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchSettings()
  }, [])

  async function handleCreate(e: FormEvent) {
    e.preventDefault()
    setMessage(null)
    setError(null)

    try {
      setIsCreating(true)

      const payload = {
        key: newSetting.key.trim(),
        value: newSetting.value,
        description: newSetting.description?.trim() || ''
      }

      const res = await apiFetch<{ data: ApiSetting }>('/admin/system-configurations/create', {
        method: 'POST',
        data: payload
      })

      setSettings(prev =>
        [...prev, res.data].sort((a, b) => a.key.localeCompare(b.key))
      )
      setNewSetting({ key: '', value: '', description: '' })
      setMessage('Đã thêm cấu hình mới.')
      setTimeout(() => setMessage(null), 3000)
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : String(err)
      setError('Lỗi khi thêm cấu hình: ' + msg)
    } finally {
      setIsCreating(false)
    }
  }

  async function handleUpdate(setting: ApiSetting) {
    setMessage(null)
    setError(null)

    try {
      setSavingKey(setting.key)
      const res = await apiFetch<{ data: ApiSetting }>(
        `/admin/system-configurations/${encodeURIComponent(setting.key)}`,
        {
          method: 'PATCH',
          data: {
            value: setting.value,
            description: setting.description ?? ''
          }
        }
      )

      setSettings(prev =>
        prev.map(item => (item.key === setting.key ? res.data : item))
      )
      setMessage('Đã cập nhật cấu hình.')
      setTimeout(() => setMessage(null), 3000)
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : String(err)
      setError('Lỗi khi cập nhật cấu hình: ' + msg)
    } finally {
      setSavingKey(null)
    }
  }

  async function handleDelete(settingKey: string) {
    const confirmed = window.confirm('Bạn có chắc muốn xóa cấu hình này?')
    if (!confirmed) return

    setMessage(null)
    setError(null)

    try {
      setDeletingKey(settingKey)
      await apiFetch(`/admin/system-configurations/${encodeURIComponent(settingKey)}`, {
        method: 'DELETE'
      })

      setSettings(prev => prev.filter(item => item.key !== settingKey))
      setMessage('Đã xóa cấu hình.')
      setTimeout(() => setMessage(null), 3000)
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : String(err)
      setError('Lỗi khi xóa cấu hình: ' + msg)
    } finally {
      setDeletingKey(null)
    }
  }

  function handleSettingChange(
    key: string,
    field: keyof ApiSetting,
    value: string
  ) {
    setSettings(prev =>
      prev.map(item => (item.key === key ? { ...item, [field]: value } : item))
    )
  }

  function handleNewSettingChange(
    field: keyof ApiSetting,
    value: string
  ) {
    setNewSetting(prev => ({ ...prev, [field]: value }))
  }

  function isSensitiveKey(key: string) {
    return /password|secret/i.test(key)
  }

  return (
    <div className="mx-auto max-w-4xl pt-4">
      {error && (
        <div className="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}
      {message && (
        <div className="mb-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {message}
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
        <div className="p-6">
          <div className="overflow-x-auto">
            <table className="w-full text-sm border border-gray-200 rounded">
              <thead className="bg-gray-50 text-gray-700">
                <tr>
                  <th className="text-left px-3 py-2 border-b">Key</th>
                  <th className="text-left px-3 py-2 border-b">Giá trị</th>
                  <th className="text-left px-3 py-2 border-b">Mô tả</th>
                  <th className="text-right px-3 py-2 border-b">Hành động</th>
                </tr>
              </thead>
              <tbody>
                {settings.length === 0 && !loading && (
                  <tr>
                    <td colSpan={4} className="px-3 py-6 text-center text-gray-500">
                      Chưa có cấu hình nào.
                    </td>
                  </tr>
                )}
                {settings.map(setting => (
                  <tr key={setting.key} className="border-t">
                    <td className="px-3 py-2 font-medium text-gray-800">{setting.key}</td>
                    <td className="px-3 py-2">
                      <input
                        type={isSensitiveKey(setting.key) ? 'password' : 'text'}
                        value={setting.value}
                        onChange={e =>
                          handleSettingChange(setting.key, 'value', e.target.value)
                        }
                        className="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                      />
                    </td>
                    <td className="px-3 py-2">
                      <input
                        type="text"
                        value={setting.description ?? ''}
                        onChange={e =>
                          handleSettingChange(setting.key, 'description', e.target.value)
                        }
                        className="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                      />
                    </td>
                    <td className="px-3 py-2 text-right space-x-2">
                      <button
                        type="button"
                        onClick={() => handleUpdate(setting)}
                        disabled={savingKey === setting.key || loading}
                        className="bg-[#1976d2] hover:bg-[#1565c0] text-white px-3 py-1.5 rounded text-xs font-medium shadow-sm transition-colors disabled:opacity-70"
                      >
                        {savingKey === setting.key ? 'Đang lưu...' : 'Lưu'}
                      </button>
                      <button
                        type="button"
                        onClick={() => handleDelete(setting.key)}
                        disabled={deletingKey === setting.key || loading}
                        className="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-medium shadow-sm transition-colors disabled:opacity-70"
                      >
                        {deletingKey === setting.key ? 'Đang xóa...' : 'Xóa'}
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <form onSubmit={handleCreate} className="mt-6 border-t pt-6">
            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div>
                <label className="block text-sm font-semibold text-gray-800 mb-1">
                  Key
                </label>
                <input
                  type="text"
                  value={newSetting.key}
                  onChange={e => handleNewSettingChange('key', e.target.value)}
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                  placeholder="vd: sender_email"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-800 mb-1">
                  Giá trị
                </label>
                <input
                  type={isSensitiveKey(newSetting.key) ? 'password' : 'text'}
                  value={newSetting.value}
                  onChange={e => handleNewSettingChange('value', e.target.value)}
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                />
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-800 mb-1">
                  Mô tả
                </label>
                <input
                  type="text"
                  value={newSetting.description ?? ''}
                  onChange={e => handleNewSettingChange('description', e.target.value)}
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                />
              </div>
            </div>
            <div className="mt-4 flex justify-end items-center gap-4">
              <button
                type="submit"
                disabled={isCreating || loading}
                className="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded text-sm font-medium shadow-sm transition-colors disabled:opacity-70"
              >
                {isCreating ? 'Đang thêm...' : 'Thêm cấu hình'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}
