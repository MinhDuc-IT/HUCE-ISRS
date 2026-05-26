const API_BASE_URL = 'http://127.0.0.1:8000/api'

interface ApiOptions extends RequestInit {
  data?: any
}

export async function apiFetch<T>(endpoint: string, options: ApiOptions = {}): Promise<T> {
  const { data, headers: customHeaders, ...customConfig } = options
  
  const token = localStorage.getItem('token')
  
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(customHeaders as Record<string, string>),
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  const config: RequestInit = {
    method: customConfig.method ?? (data ? 'POST' : 'GET'),
    ...customConfig,
    headers,
  }

  if (data) {
    config.body = JSON.stringify(data)
  }

  const response = await fetch(`${API_BASE_URL}${endpoint}`, config)

  let responseData
  const contentType = response.headers.get('content-type')
  if (contentType && contentType.includes('application/json')) {
    responseData = await response.json()
  } else {
    responseData = await response.text()
  }

  if (!response.ok) {
    // Nếu token hết hạn hoặc không hợp lệ (401), xử lý đăng xuất tại đây (hoặc văng lỗi để AuthContext bắt)
    if (response.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      // window.location.href = '/login' // Tùy chọn redirect
    }

    throw new Error(responseData.message || responseData.error || response.statusText)
  }

  return responseData as T
}
