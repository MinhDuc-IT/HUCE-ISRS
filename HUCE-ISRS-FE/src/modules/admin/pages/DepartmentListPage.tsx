import { useState, useEffect } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiDepartment } from '@/shared/types/api'

export function DepartmentListPage() {
  const [departments, setDepartments] = useState<ApiDepartment[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  // Modal state – shared for Add and Edit
  const [editingDept, setEditingDept] = useState<ApiDepartment | null>(null)
  const [isAdding, setIsAdding] = useState(false)
  const [formData, setFormData] = useState({
    code: '',
    name: '',
    email: '',
    phone: '',
  })
  const [isSubmitting, setIsSubmitting] = useState(false)

  const [search, setSearch] = useState('')

  useEffect(() => {
    fetchDepartments()
  }, [])

  async function fetchDepartments() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiDepartment[] }>('/admin/departments')
      setDepartments(res.data || [])
    } catch (err: any) {
      setError('Lỗi tải danh sách bộ môn: ' + err.message)
    } finally {
      setLoading(false)
    }
  }

  function handleEdit(dept: ApiDepartment) {
    setIsAdding(false)
    setEditingDept(dept)
    setFormData({
      code: dept.department_code,
      name: dept.department_name,
      email: dept.email ?? '',
      phone: dept.phone_number ?? '',
    })
  }

  function handleAdd() {
    setEditingDept(null)
    setIsAdding(true)
    setFormData({ code: '', name: '', email: '', phone: '' })
  }

  function handleCloseModal() {
    setEditingDept(null)
    setIsAdding(false)
  }

  async function handleDelete(id: number) {
    if (!window.confirm('Bạn có chắc chắn muốn xóa bộ môn này?')) return
    try {
      setLoading(true)
      await apiFetch(`/admin/departments/${id}`, { method: 'DELETE' })
      setMessage('Xóa bộ môn thành công.')
      setTimeout(() => setMessage(null), 3000)
      fetchDepartments()
    } catch (err: any) {
      setError('Lỗi khi xóa bộ môn: ' + err.message)
      setTimeout(() => setError(null), 3000)
    } finally {
      setLoading(false)
    }
  }

  async function handleSave(e: React.FormEvent) {
    e.preventDefault()
    try {
      setIsSubmitting(true)
      if (isAdding) {
        await apiFetch(`/admin/departments`, {
          method: 'POST',
          data: {
            department_code: formData.code.trim(),
            name: formData.name.trim(),
            email: formData.email.trim() || null,
            phone_number: formData.phone.trim() || null,
          },
        })
        setMessage('Thêm bộ môn thành công.')
      } else {
        if (!editingDept) return
        await apiFetch(`/admin/departments/${editingDept.id}`, {
          method: 'PATCH',
          data: {
            name: formData.name.trim(),
            email: formData.email.trim() || null,
            phone_number: formData.phone.trim() || null,
          },
        })
        setMessage('Cập nhật bộ môn thành công.')
      }
      setTimeout(() => setMessage(null), 3000)
      handleCloseModal()
      fetchDepartments()
    } catch (err: any) {
      setError('Lỗi khi lưu bộ môn: ' + err.message)
      setTimeout(() => setError(null), 3000)
    } finally {
      setIsSubmitting(false)
    }
  }

  const filtered = departments.filter(
    (d) =>
      d.department_code.toLowerCase().includes(search.toLowerCase()) ||
      d.department_name.toLowerCase().includes(search.toLowerCase()),
  )

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
        {/* Header */}
        <div className="p-4 border-b border-gray-200 bg-gray-50 flex flex-wrap justify-between items-center gap-3 rounded-t">
          <h2 className="text-lg font-medium text-gray-800">Danh sách bộ môn</h2>
          <div className="flex gap-2">
            <button
              onClick={handleAdd}
              className="bg-brand-500 text-white px-3 py-1.5 rounded text-sm hover:bg-brand-600 transition-colors font-medium shadow-sm"
            >
              + Thêm bộ môn
            </button>
            <input
              type="text"
              placeholder="Tìm mã hoặc tên bộ môn..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#1976d2] w-56"
            />
          </div>
        </div>

        {/* Table */}
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left text-gray-700">
            <thead className="text-xs text-gray-700 bg-gray-100 border-b border-gray-200">
              <tr>
                <th className="px-4 py-3 w-14 text-center border-r border-gray-200">TT</th>
                <th className="px-4 py-3 border-r border-gray-200 w-28">Mã BM</th>
                <th className="px-4 py-3 border-r border-gray-200">Tên bộ môn</th>
                <th className="px-4 py-3 border-r border-gray-200">Email</th>
                <th className="px-4 py-3 border-r border-gray-200">Số điện thoại</th>
                <th className="px-4 py-3 text-center w-28">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan={6} className="px-4 py-6 text-center text-gray-500">Đang tải dữ liệu...</td>
                </tr>
              ) : filtered.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-4 py-6 text-center text-gray-500 italic">Không có bộ môn nào.</td>
                </tr>
              ) : (
                filtered.map((d, index) => (
                  <tr key={d.id} className="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                    <td className="px-4 py-2 text-center border-r border-gray-100">{index + 1}</td>
                    <td className="px-4 py-2 border-r border-gray-100 font-mono text-xs font-semibold">{d.department_code}</td>
                    <td className="px-4 py-2 border-r border-gray-100">{d.department_name}</td>
                    <td className="px-4 py-2 border-r border-gray-100 text-gray-500">{d.email || '-'}</td>
                    <td className="px-4 py-2 border-r border-gray-100 text-gray-500">{d.phone || '-'}</td>
                    <td className="px-4 py-2 text-center">
                      <button
                        onClick={() => handleEdit(d)}
                        className="text-[#1976d2] hover:text-[#0d47a1] text-xs font-medium hover:underline mr-3"
                      >
                        Sửa
                      </button>
                      <button
                        onClick={() => handleDelete(d.id)}
                        className="text-red-600 hover:text-red-800 text-xs font-medium hover:underline"
                      >
                        Xóa
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal */}
      {(editingDept || isAdding) && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
          <div className="bg-white w-full max-w-lg rounded shadow-xl flex flex-col">
            {/* Modal Header */}
            <div className="bg-[#1976d2] text-white px-4 py-3 rounded-t flex justify-between items-center">
              <h3 className="font-medium text-sm flex-1 text-center">
                {isAdding ? 'Thêm bộ môn' : 'Chỉnh sửa bộ môn'}
              </h3>
              <button onClick={handleCloseModal} className="text-white hover:text-gray-200 p-1">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>

            {/* Modal Body */}
            <form onSubmit={handleSave} className="p-6 space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Mã bộ môn:</label>
                <input
                  type="text"
                  value={formData.code}
                  onChange={(e) => setFormData({ ...formData, code: e.target.value })}
                  disabled={!isAdding}
                  required
                  className={`w-full border border-gray-300 rounded px-3 py-2 text-sm ${
                    !isAdding ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'focus:outline-none focus:border-[#1976d2]'
                  }`}
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Tên bộ môn:</label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  disabled={!isAdding}
                  required
                  className={`w-full border border-gray-300 rounded px-3 py-2 text-sm ${
                    !isAdding ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'focus:outline-none focus:border-[#1976d2]'
                  }`}
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Email:</label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  placeholder="Email trưởng bộ môn"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Điện thoại trưởng bộ môn:</label>
                <input
                  type="text"
                  value={formData.phone}
                  onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                  placeholder="Số điện thoại"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2]"
                />
              </div>

              {/* Modal Footer */}
              <div className="pt-2 flex justify-end gap-2">
                <button
                  type="button"
                  onClick={handleCloseModal}
                  className="border border-gray-300 text-gray-700 px-4 py-1.5 rounded text-sm hover:bg-gray-50"
                >
                  Hủy
                </button>
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
