import { useState, useEffect } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'
import type { ApiSubject, ApiDepartment } from '@/shared/types/api'

type SubjectForm = {
  subject_code: string
  name: string
  credits: string
  department_id: string
}

const emptyForm: SubjectForm = {
  subject_code: '',
  name: '',
  credits: '',
  department_id: '',
}

export function SubjectListPage() {
  const [subjects, setSubjects] = useState<ApiSubject[]>([])
  const [departments, setDepartments] = useState<ApiDepartment[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  // Modal state – dùng chung cho Thêm và Sửa
  const [editingSubject, setEditingSubject] = useState<ApiSubject | null>(null)
  const [isAdding, setIsAdding] = useState(false)
  const [formData, setFormData] = useState<SubjectForm>(emptyForm)
  const [isSubmitting, setIsSubmitting] = useState(false)

  const [search, setSearch] = useState('')

  useEffect(() => {
    fetchSubjects()
    fetchDepartments()
  }, [])

  async function fetchSubjects() {
    try {
      setLoading(true)
      const res = await apiFetch<{ data: ApiSubject[] }>('/admin/subjects')
      setSubjects(res.data || [])
    } catch (err: any) {
      setError('Lỗi tải danh sách môn học: ' + err.message)
    } finally {
      setLoading(false)
    }
  }

  async function fetchDepartments() {
    try {
      const res = await apiFetch<{ data: ApiDepartment[] }>('/admin/departments')
      setDepartments(res.data || [])
    } catch {
      // không chặn màn hình nếu lỗi tải bộ môn — chỉ dropdown trống
    }
  }

  function departmentName(id: number): string {
    const dept = departments.find((d) => d.id === id)
    return dept ? `${dept.department_code} - ${dept.department_name}` : '-'
  }

  function handleEdit(subject: ApiSubject) {
    setIsAdding(false)
    setEditingSubject(subject)
    setFormData({
      subject_code: subject.subject_code,
      name: subject.name,
      credits: subject.credits != null ? String(subject.credits) : '',
      department_id: String(subject.department_id),
    })
  }

  function handleAdd() {
    setEditingSubject(null)
    setIsAdding(true)
    setFormData(emptyForm)
  }

  function handleCloseModal() {
    setEditingSubject(null)
    setIsAdding(false)
  }

  async function handleDelete(id: number) {
    if (!window.confirm('Bạn có chắc chắn muốn xóa môn học này?')) return
    try {
      setLoading(true)
      await apiFetch(`/admin/subjects/${id}`, { method: 'DELETE' })
      setMessage('Xóa môn học thành công.')
      setTimeout(() => setMessage(null), 3000)
      fetchSubjects()
    } catch (err: any) {
      setError('Lỗi khi xóa môn học: ' + err.message)
      setTimeout(() => setError(null), 3000)
    } finally {
      setLoading(false)
    }
  }

  async function handleSave(e: React.FormEvent) {
    e.preventDefault()
    if (!formData.department_id) {
      setError('Vui lòng chọn bộ môn.')
      setTimeout(() => setError(null), 3000)
      return
    }
    try {
      setIsSubmitting(true)
      const credits =
        formData.credits.trim() === '' ? null : Number(formData.credits)

      if (isAdding) {
        await apiFetch(`/admin/subjects`, {
          method: 'POST',
          data: {
            subject_code: formData.subject_code.trim(),
            name: formData.name.trim(),
            credits,
            department_id: Number(formData.department_id),
          },
        })
        setMessage('Thêm môn học thành công.')
      } else {
        if (!editingSubject) return
        await apiFetch(`/admin/subjects/${editingSubject.id}`, {
          method: 'PATCH',
          data: {
            name: formData.name.trim(),
            credits,
            department_id: Number(formData.department_id),
          },
        })
        setMessage('Cập nhật môn học thành công.')
      }
      setTimeout(() => setMessage(null), 3000)
      handleCloseModal()
      fetchSubjects()
    } catch (err: any) {
      setError('Lỗi khi lưu môn học: ' + err.message)
      setTimeout(() => setError(null), 3000)
    } finally {
      setIsSubmitting(false)
    }
  }

  const filtered = subjects.filter(
    (s) =>
      s.subject_code.toLowerCase().includes(search.toLowerCase()) ||
      s.name.toLowerCase().includes(search.toLowerCase()),
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
          <h2 className="text-lg font-medium text-gray-800">Danh sách môn học</h2>
          <div className="flex gap-2">
            <button
              onClick={handleAdd}
              className="bg-brand-500 text-white px-3 py-1.5 rounded text-sm hover:bg-brand-600 transition-colors font-medium shadow-sm"
            >
              + Thêm môn học
            </button>
            <input
              type="text"
              placeholder="Tìm mã hoặc tên môn học..."
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
                <th className="px-4 py-3 border-r border-gray-200 w-32">Mã môn</th>
                <th className="px-4 py-3 border-r border-gray-200">Tên môn học</th>
                <th className="px-4 py-3 border-r border-gray-200 w-24 text-center">Số TC</th>
                <th className="px-4 py-3 border-r border-gray-200">Bộ môn</th>
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
                  <td colSpan={6} className="px-4 py-6 text-center text-gray-500 italic">Không có môn học nào.</td>
                </tr>
              ) : (
                filtered.map((s, index) => (
                  <tr key={s.id} className="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                    <td className="px-4 py-2 text-center border-r border-gray-100">{index + 1}</td>
                    <td className="px-4 py-2 border-r border-gray-100 font-mono text-xs font-semibold">{s.subject_code}</td>
                    <td className="px-4 py-2 border-r border-gray-100">{s.name}</td>
                    <td className="px-4 py-2 border-r border-gray-100 text-center">{s.credits ?? '-'}</td>
                    <td className="px-4 py-2 border-r border-gray-100 text-gray-500">{departmentName(s.department_id)}</td>
                    <td className="px-4 py-2 text-center">
                      <button
                        onClick={() => handleEdit(s)}
                        className="text-[#1976d2] hover:text-[#0d47a1] text-xs font-medium hover:underline mr-3"
                      >
                        Sửa
                      </button>
                      <button
                        onClick={() => handleDelete(s.id)}
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
      {(editingSubject || isAdding) && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
          <div className="bg-white w-full max-w-lg rounded shadow-xl flex flex-col">
            {/* Modal Header */}
            <div className="bg-[#1976d2] text-white px-4 py-3 rounded-t flex justify-between items-center">
              <h3 className="font-medium text-sm flex-1 text-center">
                {isAdding ? 'Thêm môn học' : 'Chỉnh sửa môn học'}
              </h3>
              <button onClick={handleCloseModal} className="text-white hover:text-gray-200 p-1">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>

            {/* Modal Body */}
            <form onSubmit={handleSave} autoComplete="off" className="p-6 space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Mã môn học:</label>
                <input
                  type="text"
                  value={formData.subject_code}
                  onChange={(e) => setFormData({ ...formData, subject_code: e.target.value })}
                  disabled={!isAdding}
                  required
                  className={`w-full border border-gray-300 rounded px-3 py-2 text-sm ${
                    !isAdding ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'focus:outline-none focus:border-[#1976d2]'
                  }`}
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Tên môn học:</label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  required
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Số tín chỉ:</label>
                <input
                  type="number"
                  min={0}
                  value={formData.credits}
                  onChange={(e) => setFormData({ ...formData, credits: e.target.value })}
                  placeholder="Ví dụ: 3"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2]"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Bộ môn:</label>
                <select
                  value={formData.department_id}
                  onChange={(e) => setFormData({ ...formData, department_id: e.target.value })}
                  required
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:border-[#1976d2]"
                >
                  <option value="">-- Chọn bộ môn --</option>
                  {departments.map((d) => (
                    <option key={d.id} value={d.id}>
                      {d.department_code} - {d.department_name}
                    </option>
                  ))}
                </select>
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
