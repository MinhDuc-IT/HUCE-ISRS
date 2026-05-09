import { useState, useEffect } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'
import type { Department } from '@/shared/types/remedial'

export function DepartmentListPage() {
  const [departments, setDepartments] = useState<Department[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  const [editingDept, setEditingDept] = useState<Department | null>(null)
  const [formData, setFormData] = useState({
    headEmail: '',
    headPhone: '',
  })
  const [isSubmitting, setIsSubmitting] = useState(false)

  useEffect(() => {
    fetchDepartments()
  }, [])

  async function fetchDepartments() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: Department[] }>('/admin/departments')
      setDepartments(res.data || [])
    } catch (err: any) {
      setError('Lỗi tải danh sách bộ môn: ' + err.message)
    } finally {
      setLoading(false)
    }
  }

  function handleEdit(dept: Department) {
    setEditingDept(dept)
    setFormData({
      headEmail: dept.headEmail ?? '',
      headPhone: dept.headPhone ?? '',
    })
  }

  function handleCloseModal() {
    setEditingDept(null)
  }

  async function handleSave(e: React.FormEvent) {
    e.preventDefault()
    if (!editingDept) return

    try {
      setIsSubmitting(true)
      await apiFetch(`/admin/departments/${editingDept.id}`, {
        method: 'PATCH',
        data: {
          headEmail: formData.headEmail.trim(),
          headPhone: formData.headPhone.trim(),
        }
      })
      
      setMessage('Cập nhật bộ môn thành công.')
      setTimeout(() => setMessage(null), 3000)
      setEditingDept(null)
      fetchDepartments()
    } catch (err: any) {
      setError('Lỗi khi cập nhật bộ môn: ' + err.message)
      setTimeout(() => setError(null), 3000)
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <div className="space-y-4 relative">
      {error && (
        <div className="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}
      {message && (
        <div className="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {message}
        </div>
      )}

      <div className="bg-white rounded border border-gray-200 shadow-sm">
        <div className="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t">
          <h2 className="text-lg font-medium text-gray-800 flex items-center gap-2">
            Danh sách bộ môn
          </h2>
          <div className="flex gap-2">
            <input 
              type="text" 
              placeholder="Nhập mã bộ môn..." 
              className="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#1976d2]"
            />
            <button className="bg-[#547294] text-white px-3 py-1.5 rounded text-sm hover:bg-[#46658b] transition-colors flex items-center">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left text-gray-700">
            <thead className="text-xs text-gray-700 bg-gray-100 border-b border-gray-200">
              <tr>
                <th className="px-4 py-3 w-16 text-center border-r border-gray-200">TT</th>
                <th className="px-4 py-3 border-r border-gray-200 w-24 text-center">Mã BM</th>
                <th className="px-4 py-3 border-r border-gray-200">Tên bộ môn</th>
                <th className="px-4 py-3 border-r border-gray-200">Email trưởng BM</th>
                <th className="px-4 py-3 border-r border-gray-200">SĐT trưởng BM</th>
                <th className="px-4 py-3 text-center w-24">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan={6} className="px-4 py-6 text-center text-gray-500">Đang tải dữ liệu...</td>
                </tr>
              ) : departments.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-4 py-6 text-center text-gray-500 italic">Không có bộ môn nào.</td>
                </tr>
              ) : (
                departments.map((d, index) => (
                  <tr key={d.id} className="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                    <td className="px-4 py-2 text-center border-r border-gray-100">{index + 1}</td>
                    <td className="px-4 py-2 text-center border-r border-gray-100 font-mono text-xs">{d.code}</td>
                    <td className="px-4 py-2 border-r border-gray-100">{d.name}</td>
                    <td className="px-4 py-2 border-r border-gray-100">{d.headEmail || '-'}</td>
                    <td className="px-4 py-2 border-r border-gray-100">{d.headPhone || '-'}</td>
                    <td className="px-4 py-2 text-center">
                      <button
                        onClick={() => handleEdit(d)}
                        className="text-[#1976d2] hover:text-[#0d47a1] text-xs font-medium hover:underline"
                      >
                        Chỉnh sửa
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Overlay */}
      {editingDept && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
          {/* Modal Content */}
          <div className="bg-white w-full max-w-lg rounded shadow-xl flex flex-col animate-in fade-in zoom-in duration-200">
            {/* Modal Header */}
            <div className="bg-[#1976d2] text-white px-4 py-3 rounded-t flex justify-between items-center">
              <h3 className="font-medium text-sm text-center flex-1">Chi tiết bộ môn</h3>
              <button 
                onClick={handleCloseModal}
                className="text-white hover:text-gray-200 rounded-full p-1 leading-none"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              </button>
            </div>

            {/* Modal Body */}
            <form onSubmit={handleSave} className="p-6 space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Mã bộ môn:</label>
                <input
                  type="text"
                  value={editingDept.code}
                  disabled
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100 text-gray-500 cursor-not-allowed"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Tên bộ môn:</label>
                <input
                  type="text"
                  value={editingDept.name}
                  disabled
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100 text-gray-500 cursor-not-allowed"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Email:</label>
                <input
                  type="email"
                  value={formData.headEmail}
                  onChange={(e) => setFormData({ ...formData, headEmail: e.target.value })}
                  placeholder="Email trưởng bộ môn"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Điện thoại trưởng bộ môn:</label>
                <input
                  type="text"
                  value={formData.headPhone}
                  onChange={(e) => setFormData({ ...formData, headPhone: e.target.value })}
                  placeholder="Điện thoại trưởng bộ môn"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                />
              </div>

              {/* Modal Footer */}
              <div className="pt-2 flex justify-end">
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="bg-[#1976d2] hover:bg-[#1565c0] text-white px-6 py-1.5 rounded text-sm font-medium shadow-sm transition-colors disabled:opacity-70"
                >
                  {isSubmitting ? 'Đang lưu...' : 'Lưu'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
