/**
 * Configured Axios instance.
 * Re-exported from src/api/index.ts so both import paths work:
 *   import api from '@/api'          (via index.ts)
 *   import api from '@/api/axios'    (direct)
 */
import axios from 'axios'
import { ElMessage } from 'element-plus'

const api = axios.create({
  baseURL: '/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  withCredentials: true,
})

// ── Request interceptor — attach Bearer token + locale ────────────────────
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  // Forward current locale to backend (Accept-Language)
  const locale = localStorage.getItem('locale') ?? 'en'
  config.headers['Accept-Language'] = locale

  return config
})

// ── Response interceptor — handle common HTTP errors ──────────────────────
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      // Token expired or invalid — clear local auth and redirect to login
      localStorage.removeItem('auth_token')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
      return Promise.reject(error)
    }

    if (status === 403) {
      ElMessage.error('You do not have permission to perform this action.')
      return Promise.reject(error)
    }

    if (status === 422) {
      // Validation errors — let the calling component handle field-level errors
      return Promise.reject(error)
    }

    if (status === 500) {
      ElMessage.error('A server error occurred. Please try again later.')
    }

    return Promise.reject(error)
  },
)

export default api
