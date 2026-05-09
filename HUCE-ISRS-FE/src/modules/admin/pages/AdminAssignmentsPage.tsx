import { useState, useEffect } from 'react'
import { apiFetch } from '@/shared/utils/apiClient'

interface ApiCourse {
  id: string
  code: string
  name: string
  credits: number
}

interface ApiTutoringClass {
  id: string
  course_id: string
  cohort_id: string
  status: number
  teacher_id?: string
  teacher?: {
    id: string
    name: string
    email: string
    phone: string
  }
  course?: ApiCourse
}

interface ApiUser {
  id: string
  name: string
  email: string
  phone?: string
}

export function AdminAssignmentsPage() {
  const [tutoringClasses, setTutoringClasses] = useState<ApiTutoringClass[]>([])
  const [teachers, setTeachers] = useState<ApiUser[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)
  
  const [editingRow, setEditingRow] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  const [formData, setFormData] = useState({
    teacher_id: '',
    phone: '',
    email: ''
  })

  useEffect(() => {
    fetchData()
  }, [])

  async function fetchData() {
    try {
      setLoading(true)
      // Fetch tutoring classes and a list of users (teachers)
      const [classesRes, usersRes] = await Promise.all([
        apiFetch<{ data: ApiTutoringClass[] }>('/admin/tutoring-classes'),
        // Tạm gọi danh sách users để lấy teacher, ở Backend có thể cần API riêng để lọc teacher
        apiFetch<{ data: ApiUser[] }>('/admin/users')
      ])
      
      setTutoringClasses(classesRes.data || [])
      // Lọc các user là giảng viên (role_id = ...) - Tạm lấy toàn bộ để demo mapping
      setTeachers(usersRes.data || [])
    } catch (err: any) {
      setError('Lỗi tải dữ liệu: ' + err.message)
    } finally {
      setLoading(false)
    }
  }

  function handleEdit(tClass: ApiTutoringClass) {
    setEditingRow(tClass.id)
    setFormData({
      teacher_id: tClass.teacher_id?.toString() || '',
      phone: tClass.teacher?.phone || '',
      email: tClass.teacher?.email || ''
    })
  }

  function handleTeacherSelect(e: React.ChangeEvent<HTMLSelectElement>) {
    const selectedId = e.target.value
    const selectedTeacher = teachers.find(t => t.id.toString() === selectedId)
    
    setFormData({
      teacher_id: selectedId,
      phone: selectedTeacher?.phone || '',
      email: selectedTeacher?.email || ''
    })
  }

  async function handleSave(e: React.FormEvent) {
    e.preventDefault()
    if (!editingRow) return

    try {
      setIsSubmitting(true)
      
      await apiFetch(`/admin/tutoring-classes/${editingRow}/assign-teacher`, {
        method: 'PATCH',
        data: {
          teacher_id: formData.teacher_id
        }
      })
      
      setMessage('Phân công giảng viên thành công.')
      setTimeout(() => setMessage(null), 3000)
      setEditingRow(null)
      fetchData()
    } catch (err: any) {
      setError('Lỗi phân công giảng viên: ' + err.message)
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
        <div className="p-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4 rounded-t">
          <div className="flex items-center gap-2 text-sm text-gray-700">
            <span className="font-semibold">Đợt phụ đạo:</span>
            <span className="text-gray-500">Tất cả (Lấy từ API)</span>
          </div>
          <div className="flex gap-2">
            <input 
              type="text" 
              placeholder="Tên môn học..." 
              className="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#1976d2] w-64"
            />
            <button className="bg-[#547294] text-white px-3 py-1.5 rounded text-sm hover:bg-[#46658b] transition-colors flex items-center">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
          </div>
        </div>

        <div className="p-3 text-center text-gray-600 font-medium bg-gray-50/50">
          Danh sách môn học đăng ký phụ đạo
        </div>

        <div className="overflow-x-auto relative">
          {loading && (
            <div className="absolute inset-0 bg-white/60 z-10 flex items-center justify-center backdrop-blur-[1px]">
              <span className="text-gray-500 font-medium">Đang tải dữ liệu...</span>
            </div>
          )}
          <table className="w-full text-sm text-left text-gray-700">
            <thead className="text-xs text-gray-700 bg-gray-100 border-y border-gray-200 text-center">
              <tr>
                <th className="px-3 py-3 w-12 border-r border-gray-200">TT</th>
                <th className="px-3 py-3 border-r border-gray-200">Mã MH</th>
                <th className="px-3 py-3 border-r border-gray-200">Tên môn học</th>
                <th className="px-3 py-3 border-r border-gray-200">Lớp MH</th>
                <th className="px-3 py-3 border-r border-gray-200">STC</th>
                <th className="px-3 py-3 border-r border-gray-200">Trạng thái</th>
                <th className="px-3 py-3 border-r border-gray-200">GV phụ đạo</th>
                <th className="px-3 py-3 border-r border-gray-200">Phân công BM</th>
                <th className="px-3 py-3">Phân công GV</th>
              </tr>
            </thead>
            <tbody>
              {tutoringClasses.length === 0 && !loading ? (
                <tr>
                  <td colSpan={9} className="px-4 py-6 text-center text-gray-500 italic">
                    Không có lớp phụ đạo nào.
                  </td>
                </tr>
              ) : (
                tutoringClasses.map((c, index) => (
                  <tr key={c.id} className="border-b border-gray-100 hover:bg-gray-50 text-center">
                    <td className="px-3 py-2 border-r border-gray-100">{index + 1}</td>
                    <td className="px-3 py-2 border-r border-gray-100 text-[#1976d2]">{c.course?.code || 'N/A'}</td>
                    <td className="px-3 py-2 border-r border-gray-100 text-left">{c.course?.name || 'N/A'}</td>
                    <td className="px-3 py-2 border-r border-gray-100">N/A</td>
                    <td className="px-3 py-2 border-r border-gray-100">{c.course?.credits || 0}</td>
                    <td className="px-3 py-2 border-r border-gray-100">
                      {c.status === 1 ? 'Mới' : c.status === 2 ? 'Đang mở' : 'Đóng'}
                    </td>
                    <td className="px-3 py-2 border-r border-gray-100">
                      {c.teacher ? c.teacher.name : <span className="text-gray-400 italic">Chưa phân công</span>}
                    </td>
                    <td className="px-3 py-2 border-r border-gray-100">
                      <button className="text-gray-400 hover:text-[#1976d2] p-1 transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                      </button>
                    </td>
                    <td className="px-3 py-2">
                      <div className="flex items-center justify-center gap-2">
                        <button 
                          onClick={() => handleEdit(c)}
                          className="text-gray-400 hover:text-[#1976d2] p-1 transition-colors"
                        >
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Overlay */}
      {editingRow && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
          {/* Modal Content */}
          <div className="bg-white w-full max-w-md rounded shadow-xl flex flex-col animate-in fade-in zoom-in duration-200">
            {/* Modal Header */}
            <div className="bg-[#1976d2] text-white px-4 py-3 rounded-t flex justify-between items-center">
              <h3 className="font-medium text-sm text-center flex-1">Nhập giảng viên phụ đạo</h3>
              <button 
                onClick={() => setEditingRow(null)}
                className="text-white hover:text-gray-200 rounded-full p-1 leading-none"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              </button>
            </div>

            {/* Modal Body */}
            <form onSubmit={handleSave} className="p-6 space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Giảng viên:</label>
                <select
                  value={formData.teacher_id}
                  onChange={handleTeacherSelect}
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                  required
                >
                  <option value="" disabled>-- Chọn giảng viên --</option>
                  {teachers.map(t => (
                    <option key={t.id} value={t.id}>{t.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Điện thoại:</label>
                <input
                  type="text"
                  value={formData.phone}
                  readOnly
                  placeholder="Điện thoại (tự động)"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100 text-gray-500 cursor-not-allowed"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Email:</label>
                <input
                  type="email"
                  value={formData.email}
                  readOnly
                  placeholder="Email (tự động)"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100 text-gray-500 cursor-not-allowed"
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
