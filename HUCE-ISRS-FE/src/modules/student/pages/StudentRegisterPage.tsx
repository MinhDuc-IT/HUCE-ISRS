import { useMemo, useState, useEffect } from 'react'
import { useAuth } from '@/shared/context/AuthContext'
import { apiFetch } from '@/shared/utils/apiClient'

// Định nghĩa types từ Backend API
interface ApiCourse {
  id: string
  code: string
  name: string
  credits: number
  pivot?: {
    tutoring_class_id: string
    course_id: string
  }
}

interface ApiTutoringClass {
  id: string
  course_id: string
  cohort_id: string
  status: number
  course?: ApiCourse
}

interface ApiRegistration {
  id: string
  student_id: string
  tutoring_class_id: string
  status: number
  created_at: string
  tutoring_class?: ApiTutoringClass
}

export function StudentRegisterPage() {
  const { user } = useAuth()
  
  // State chứa data từ API
  const [registrations, setRegistrations] = useState<ApiRegistration[]>([])
  const [eligibleCourses, setEligibleCourses] = useState<ApiCourse[]>([])
  const [loadingData, setLoadingData] = useState(true)

  const [selectedToRegister, setSelectedToRegister] = useState<Record<string, boolean>>({})
  const [selectedToCancel, setSelectedToCancel] = useState<Record<string, boolean>>({})
  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  // Fetch data lần đầu
  useEffect(() => {
    if (!user?.id) return
    fetchData()
  }, [user?.id])

  async function fetchData() {
    if (!user?.id) return
    try {
      setLoadingData(true)
      const [regsResponse, coursesResponse] = await Promise.all([
        apiFetch<{ data: ApiRegistration[] }>(`/students/${user.id}/registrations`),
        apiFetch<{ data: ApiCourse[] }>(`/students/${user.id}/eligible-courses`)
      ])
      
      setRegistrations(regsResponse.data || [])
      setEligibleCourses(coursesResponse.data || [])
    } catch (err: any) {
      setError('Lỗi khi tải dữ liệu từ máy chủ: ' + err.message)
    } finally {
      setLoadingData(false)
    }
  }

  // Set các ID môn học đã đăng ký để disable các ô checkbox
  const registeredCourseIds = useMemo(() => {
    const ids = new Set<string>()
    registrations.forEach(r => {
      if (r.tutoring_class && r.tutoring_class.course_id) {
        ids.add(r.tutoring_class.course_id)
      }
    })
    return ids
  }, [registrations])

  function handleToggleRegister(courseId: string, checked: boolean) {
    setSelectedToRegister((s) => ({ ...s, [courseId]: checked }))
  }

  function handleToggleCancel(registrationId: string, checked: boolean) {
    setSelectedToCancel((s) => ({ ...s, [registrationId]: checked }))
  }

  async function handleSaveRegistration() {
    setMessage(null)
    setError(null)
    if (!user) return

    const courseIdsToRegister = Object.entries(selectedToRegister)
      .filter(([, v]) => v)
      .map(([k]) => k)

    if (courseIdsToRegister.length === 0) {
      setError('Vui lòng chọn ít nhất 1 môn để đăng ký.')
      return
    }

    try {
      setIsSubmitting(true)
      
      // Hiện tại API POST /registrations nhận một đăng ký mỗi lần. Ta duyệt mảng.
      // Cần class_id chứ không phải course_id theo backend. 
      // Ở mock, course có pivot.tutoring_class_id.
      let successCount = 0
      
      for (const courseId of courseIdsToRegister) {
        const course = eligibleCourses.find(c => c.id === courseId)
        const classId = course?.pivot?.tutoring_class_id
        
        if (classId) {
          await apiFetch('/registrations', {
            method: 'POST',
            data: {
              student_id: user.id,
              tutoring_class_id: classId
            }
          })
          successCount++
        }
      }

      if (successCount > 0) {
        setMessage(`Đã đăng ký thành công ${successCount} môn học.`)
        setSelectedToRegister({})
        fetchData() // Reload
      } else {
        setError('Không tìm thấy lớp phụ đạo cho môn này.')
      }

    } catch (err: any) {
      setError('Lỗi khi lưu đăng ký: ' + err.message)
    } finally {
      setIsSubmitting(false)
    }
  }

  async function handleDeleteRegistration() {
    setMessage(null)
    setError(null)
    
    const regIdsToCancel = Object.entries(selectedToCancel)
      .filter(([, v]) => v)
      .map(([k]) => k)
      
    if (regIdsToCancel.length === 0) {
      setError('Vui lòng chọn ít nhất 1 môn để hủy đăng ký.')
      return
    }
    
    try {
      setIsSubmitting(true)
      for (const regId of regIdsToCancel) {
        await apiFetch(`/registrations/${regId}`, {
          method: 'DELETE'
        })
      }
      
      setMessage('Đã hủy đăng ký thành công.')
      setSelectedToCancel({})
      fetchData() // Reload
    } catch (err: any) {
      setError('Lỗi khi hủy đăng ký: ' + err.message)
    } finally {
      setIsSubmitting(false)
    }
  }

  if (!user) return null

  return (
    <div className="space-y-6">
      {/* Thông báo lỗi / thành công */}
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

      {/* Phần 1: Danh sách môn học đã đăng ký */}
      <div className="bg-white rounded border border-gray-200 shadow-sm">
        <div className="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t">
          <h2 className="text-lg font-medium text-gray-800">
            Danh sách môn học đã đăng ký
          </h2>
          <button
            onClick={handleDeleteRegistration}
            disabled={isSubmitting || loadingData}
            className="bg-[#1976d2] hover:bg-[#1565c0] text-white px-4 py-1.5 rounded text-sm font-medium transition-colors flex items-center gap-2 disabled:opacity-70"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
            {isSubmitting ? 'Đang xóa...' : 'Xóa đăng ký'}
          </button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left text-gray-700">
            <thead className="text-xs text-gray-700 bg-gray-100 border-b border-gray-200 text-center">
              <tr>
                <th className="px-4 py-3 w-12">Chọn</th>
                <th className="px-4 py-3 border-l border-gray-200">Mã MH</th>
                <th className="px-4 py-3 border-l border-gray-200">Tên môn học</th>
                <th className="px-4 py-3 border-l border-gray-200">STC</th>
                <th className="px-4 py-3 border-l border-gray-200">Trạng thái</th>
                <th className="px-4 py-3 border-l border-gray-200">Ngày đăng ký</th>
              </tr>
            </thead>
            <tbody>
              {loadingData ? (
                <tr>
                  <td colSpan={6} className="px-4 py-6 text-center text-gray-500">Đang tải dữ liệu...</td>
                </tr>
              ) : registrations.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-4 py-6 text-center text-gray-500 italic">
                    Chưa đăng ký môn học nào.
                  </td>
                </tr>
              ) : (
                registrations.map((reg) => {
                  const course = reg.tutoring_class?.course
                  return (
                    <tr key={reg.id} className="border-b border-gray-100 hover:bg-gray-50 text-center">
                      <td className="px-4 py-2 border-r border-gray-100">
                        <input
                          type="checkbox"
                          className="w-4 h-4 rounded border-gray-300"
                          checked={Boolean(selectedToCancel[reg.id])}
                          onChange={(e) => handleToggleCancel(reg.id, e.target.checked)}
                        />
                      </td>
                      <td className="px-4 py-2 border-r border-gray-100">{course?.code || 'N/A'}</td>
                      <td className="px-4 py-2 border-r border-gray-100 text-left">{course?.name || 'N/A'}</td>
                      <td className="px-4 py-2 border-r border-gray-100">{course?.credits || 0}</td>
                      <td className="px-4 py-2 border-r border-gray-100">
                        {reg.status === 1 ? 'Chờ duyệt' : reg.status === 2 ? 'Đã duyệt' : 'N/A'}
                      </td>
                      <td className="px-4 py-2 border-r border-gray-100">
                        {new Date(reg.created_at).toLocaleDateString('vi-VN')}
                      </td>
                    </tr>
                  )
                })
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Phần 2: Danh sách môn học đợt đăng ký */}
      <div className="bg-white rounded border border-gray-200 shadow-sm mt-8">
        <div className="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t">
          <h2 className="text-lg font-medium text-gray-800">
            Danh sách môn học được phép đăng ký
          </h2>
          <button
            onClick={handleSaveRegistration}
            disabled={isSubmitting || loadingData}
            className="bg-[#1976d2] hover:bg-[#1565c0] text-white px-4 py-1.5 rounded text-sm font-medium transition-colors flex items-center gap-2 disabled:opacity-70"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            {isSubmitting ? 'Đang lưu...' : 'Lưu đăng ký'}
          </button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left text-gray-700">
            <thead className="text-xs text-gray-700 bg-gray-100 border-b border-gray-200 text-center">
              <tr>
                <th className="px-4 py-3 w-12">Chọn</th>
                <th className="px-4 py-3 border-l border-gray-200">Mã MH</th>
                <th className="px-4 py-3 border-l border-gray-200">Tên môn học</th>
                <th className="px-4 py-3 border-l border-gray-200">STC</th>
              </tr>
            </thead>
            <tbody>
              {loadingData ? (
                <tr>
                  <td colSpan={4} className="px-4 py-6 text-center text-gray-500">Đang tải dữ liệu...</td>
                </tr>
              ) : eligibleCourses.length === 0 ? (
                <tr>
                  <td colSpan={4} className="px-4 py-6 text-center text-gray-500 italic">
                    Không có môn học nào bạn được phép đăng ký lúc này.
                  </td>
                </tr>
              ) : (
                eligibleCourses.map((course) => {
                  const isRegistered = registeredCourseIds.has(course.id)
                  return (
                    <tr key={course.id} className={`border-b border-gray-100 hover:bg-gray-50 text-center ${isRegistered ? 'bg-green-50/30' : ''}`}>
                      <td className="px-4 py-2 border-r border-gray-100">
                        <input
                          type="checkbox"
                          className="w-4 h-4 rounded border-gray-300"
                          disabled={isRegistered}
                          checked={isRegistered || Boolean(selectedToRegister[course.id])}
                          onChange={(e) => handleToggleRegister(course.id, e.target.checked)}
                        />
                      </td>
                      <td className="px-4 py-2 border-r border-gray-100">{course.code}</td>
                      <td className="px-4 py-2 border-r border-gray-100 text-left">{course.name}</td>
                      <td className="px-4 py-2 border-r border-gray-100">{course.credits}</td>
                    </tr>
                  )
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
