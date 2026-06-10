import { useState, useEffect } from 'react'
import { apiFetch, API_BASE_URL } from '@/shared/utils/apiClient'

function formatVnd(n: number) {
  return new Intl.NumberFormat('vi-VN').format(n)
}

interface TermOption {
  id: number
  name: string
}

interface PaymentRecord {
  id: number
  lecturer_name: string | null
  lecturer_phone: string | null
  subject_code: string
  subject_name: string
  remedial_periods: number
  price_per_period: number
  base_amount: number
  total_amount: number
}

interface PaginatedResponse {
  data: PaymentRecord[]
  total: number
  per_page: number
  current_page: number
  last_page: number
}

export function TeachingPaymentsPage() {
  const [terms, setTerms] = useState<TermOption[]>([])
  const [selectedTermId, setSelectedTermId] = useState('')
  const [keyword, setKeyword] = useState('')
  const [loadingTerms, setLoadingTerms] = useState(true)
  const [loadingData, setLoadingData] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const [data, setData] = useState<PaymentRecord[]>([])
  const [currentPage, setCurrentPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  
  const pageTotal = data.reduce((acc, curr) => acc + curr.total_amount, 0)

  useEffect(() => {
    fetchTerms()
  }, [])

  useEffect(() => {
    if (selectedTermId) {
      fetchData(selectedTermId, keyword, currentPage)
    } else {
      setData([])
    }
  }, [selectedTermId, currentPage])

  async function fetchTerms() {
    try {
      setLoadingTerms(true)
      const res = await apiFetch<{ data: { terms: TermOption[] } }>('/admin/statistics/terms')
      const fetchedTerms = res.data?.terms || []
      setTerms(fetchedTerms)
      if (fetchedTerms.length > 0) {
        setSelectedTermId(String(fetchedTerms[0].id))
      }
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Lỗi khi tải danh sách đợt phụ đạo: ' + msg)
    } finally {
      setLoadingTerms(false)
    }
  }

  async function fetchData(termId: string, kw: string, page: number) {
    try {
      setLoadingData(true)
      const params = new URLSearchParams()
      if (kw) params.append('keyword', kw)
      params.append('page', String(page))
      params.append('per_page', '15')

      const res = await apiFetch<{ data: PaginatedResponse }>(
        `/admin/statistics/terms/${termId}/teaching-payments?${params.toString()}`
      )
      setData(res.data.data)
      setCurrentPage(res.data.current_page)
      setLastPage(res.data.last_page)
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
      setError('Lỗi khi tải dữ liệu: ' + msg)
    } finally {
      setLoadingData(false)
    }
  }

  function handleSearch(e: React.FormEvent) {
    e.preventDefault()
    setCurrentPage(1)
    if (selectedTermId) {
      fetchData(selectedTermId, keyword, 1)
    }
  }

  function handleExport() {
    if (!selectedTermId) return
    const params = new URLSearchParams()
    if (keyword) params.append('keyword', keyword)
    exportExcel(selectedTermId, params.toString())
  }

  async function exportExcel(termId: string, queryString: string) {
    try {
      setLoadingData(true)
      const url = `${API_BASE_URL}/admin/statistics/terms/${termId}/teaching-payments/export?${queryString}`
      const token = localStorage.getItem('token')
      const response = await fetch(url, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      })
      if (!response.ok) throw new Error('Không thể tải file')
      const blob = await response.blob()
      const downloadUrl = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = downloadUrl
      a.download = `teaching_payments_term_${termId}.xlsx`
      document.body.appendChild(a)
      a.click()
      a.remove()
      window.URL.revokeObjectURL(downloadUrl)
    } catch (err: unknown) {
       const msg = err instanceof Error ? err.message : 'Lỗi không xác định'
       setError('Lỗi xuất file: ' + msg)
    } finally {
      setLoadingData(false)
    }
  }

  const selectedTerm = terms.find(t => String(t.id) === selectedTermId)

  return (
    <div className="space-y-4">
      {error && (
        <div className="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      <div className="flex flex-row gap-4 justify-between items-center bg-white p-4 shadow-sm rounded-lg border border-gray-200">
        <div className="flex items-center gap-2">
          <label className="text-sm font-medium text-gray-700 whitespace-nowrap">Đợt phụ đạo:</label>
          <select
            className="rounded border border-gray-300 px-3 py-1.5 text-sm min-w-[250px]"
            value={selectedTermId}
            onChange={(e) => {
              setSelectedTermId(e.target.value)
              setCurrentPage(1)
            }}
            disabled={loadingTerms || terms.length === 0}
          >
            {terms.map((c) => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>
        </div>

        <form onSubmit={handleSearch} className="flex ml-auto">
          <input
            type="text"
            placeholder="Tên giảng viên..."
            className="rounded-l border border-gray-300 px-3 py-1.5 text-sm w-[200px]"
            value={keyword}
            onChange={(e) => setKeyword(e.target.value)}
          />
          <button
            type="submit"
            className="text-white px-3 py-1.5 rounded-r flex items-center justify-center cursor-pointer"
            style={{ backgroundColor: '#2563eb' }}
            disabled={loadingData}
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>
        </form>
      </div>

      <div className="bg-white rounded-lg shadow border border-gray-200 p-4">
        <div className="text-center mb-4">
          <h2 className="text-lg font-bold text-gray-800 uppercase">
            Bảng tổng hợp thanh toán giảng dạy phụ đạo
          </h2>
          <p className="text-sm text-gray-600">{selectedTerm?.name}</p>
        </div>

        <div className="overflow-x-auto">
          <table className="min-w-full text-sm border-collapse border border-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="border border-gray-200 px-3 py-2 font-medium text-center text-gray-700">TT</th>
                <th className="border border-gray-200 px-3 py-2 font-medium text-center text-gray-700">GV phụ đạo</th>
                <th className="border border-gray-200 px-3 py-2 font-medium text-center text-gray-700">SĐT GV</th>
                <th className="border border-gray-200 px-3 py-2 font-medium text-center text-gray-700">Mã MH</th>
                <th className="border border-gray-200 px-3 py-2 font-medium text-center text-gray-700">Tên MH</th>
                <th className="border border-gray-200 px-3 py-2 font-medium text-center text-gray-700">ST PĐ</th>
                <th className="border border-gray-200 px-3 py-2 font-medium text-center text-gray-700">Đơn giá/ 1 tiết</th>
                <th className="border border-gray-200 px-3 py-2 font-medium text-center text-gray-700">Số tiền (*hệ số 1)</th>
                <th className="border border-gray-200 px-3 py-2 font-medium text-center text-gray-700">Tổng tiền</th>
              </tr>
            </thead>
            <tbody>
              {loadingData ? (
                <tr>
                  <td colSpan={9} className="border border-gray-200 px-3 py-6 text-center text-gray-500">
                    Đang tải dữ liệu...
                  </td>
                </tr>
              ) : data.length === 0 ? (
                <tr>
                  <td colSpan={9} className="border border-gray-200 px-3 py-6 text-center text-gray-500">
                    Không có dữ liệu
                  </td>
                </tr>
              ) : (
                data.map((item, idx) => (
                  <tr key={idx} className="hover:bg-gray-50 text-center">
                    <td className="border border-gray-200 px-3 py-2">{(currentPage - 1) * 15 + idx + 1}</td>
                    <td className="border border-gray-200 px-3 py-2 text-left">{item.lecturer_name}</td>
                    <td className="border border-gray-200 px-3 py-2">{item.lecturer_phone}</td>
                    <td className="border border-gray-200 px-3 py-2">{item.subject_code}</td>
                    <td className="border border-gray-200 px-3 py-2 text-left">{item.subject_name}</td>
                    <td className="border border-gray-200 px-3 py-2">{item.remedial_periods}</td>
                    <td className="border border-gray-200 px-3 py-2">{formatVnd(item.price_per_period)}</td>
                    <td className="border border-gray-200 px-3 py-2">{formatVnd(item.base_amount)}</td>
                    <td className="border border-gray-200 px-3 py-2">{formatVnd(item.total_amount)}</td>
                  </tr>
                ))
              )}
              {!loadingData && data.length > 0 && (
                <tr className="bg-gray-50 font-bold">
                  <td colSpan={8} className="border border-gray-200 px-3 py-2 text-right">
                    Tổng tiền
                  </td>
                  <td className="border border-gray-200 px-3 py-2 text-center text-blue-700">
                    {formatVnd(pageTotal)}
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        <div className="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex justify-start">
             <button
                onClick={handleExport}
                disabled={loadingData || !selectedTermId}
                className="text-white px-4 py-1.5 rounded shadow-sm flex items-center gap-2 text-sm font-medium cursor-pointer hover:opacity-90"
                style={{ backgroundColor: '#5bc0de', border: '1px solid #46b8da' }}
             >
                Xuất excel
             </button>
          </div>

          <div className="flex items-center gap-4 ml-auto">
            <div className="text-sm text-gray-600">
              Trang {currentPage} / {lastPage > 0 ? lastPage : 1}
            </div>
            
            {lastPage > 1 && (
              <div className="flex gap-1">
                <button
                  disabled={currentPage === 1 || loadingData}
                  onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                  className="px-3 py-1 rounded border border-gray-300 disabled:opacity-50 hover:bg-gray-50 text-sm"
                >
                  Trước
                </button>
                <button
                  disabled={currentPage === lastPage || loadingData}
                  onClick={() => setCurrentPage(prev => Math.min(lastPage, prev + 1))}
                  className="px-3 py-1 rounded border border-gray-300 disabled:opacity-50 hover:bg-gray-50 text-sm"
                >
                  Sau
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
