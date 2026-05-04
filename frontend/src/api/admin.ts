/**
 * Admin portal API calls.
 */
import api from './axios'

// ─── Interfaces ───────────────────────────────────────────────────────────────

export type StatsPeriod = '7d' | '30d' | '90d' | '1y' | 'all'

export interface UserParams {
  search?: string
  role?: string
  status?: string
  per_page?: number
  page?: number
}

export interface CreateAdminData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface ResumeParams {
  search?: string
  status?: string
  date_from?: string
  date_to?: string
  per_page?: number
  page?: number
}

export interface InterviewParams {
  search?: string
  status?: string
  date_from?: string
  date_to?: string
  per_page?: number
  page?: number
}

export interface AdminSeminarParams {
  search?: string
  status?: string
  per_page?: number
  page?: number
}

export interface SeminarFormData {
  title_en: string
  title_zh_cn: string
  title_th: string
  description_en?: string
  description_zh_cn?: string
  description_th?: string
  speaker_name?: string
  speaker_title?: string
  speaker_bio?: string
  scheduled_at: string
  duration: number
  language?: string
  max_registrations?: number
}

// ─── API module ───────────────────────────────────────────────────────────────

export const adminApi = {
  /** Dashboard statistics */
  getStats: (period: StatsPeriod = '30d') =>
    api.get('/admin/stats', { params: { period } }),

  /** List all users (paginated, filterable) */
  getUsers: (params?: UserParams) =>
    api.get('/admin/users', { params }),

  /** Get single user detail */
  getUser: (id: number) =>
    api.get(`/admin/users/${id}`),

  /** Activate or suspend a user */
  updateUserStatus: (id: number, status: 'active' | 'suspended') =>
    api.put(`/admin/users/${id}/status`, { status }),

  /** Approve an enterprise account */
  approveEnterprise: (id: number) =>
    api.put(`/admin/users/${id}/approve-enterprise`),

  /** Delete a user */
  deleteUser: (id: number) =>
    api.delete(`/admin/users/${id}`),

  /** Create a new admin user */
  createAdmin: (data: CreateAdminData) =>
    api.post('/admin/users', { ...data, role: 'admin' }),

  // ─── Resumes ───────────────────────────────────────────────────────────────

  /** List all resumes (paginated, filterable) */
  getResumes: (params?: ResumeParams) =>
    api.get('/admin/resumes', { params }),

  /** Get resume detail including presigned download URL */
  getResume: (id: number) =>
    api.get(`/admin/resumes/${id}`),

  /** Approve a resume */
  approveResume: (id: number) =>
    api.put(`/admin/resumes/${id}/approve`),

  /** Reject a resume with reason */
  rejectResume: (id: number, reason: string) =>
    api.put(`/admin/resumes/${id}/reject`, { reason }),

  // ─── Interviews ────────────────────────────────────────────────────────────

  /** List all interviews (admin) */
  getInterviews: (params?: InterviewParams) =>
    api.get('/admin/interviews', { params }),

  /** Cancel an interview (admin) */
  cancelInterview: (id: number) =>
    api.put(`/admin/interviews/${id}/cancel`),

  // ─── Seminars ──────────────────────────────────────────────────────────────

  /** List all seminars (admin) */
  getSeminars: (params?: AdminSeminarParams) =>
    api.get('/admin/seminars', { params }),

  /** Create a seminar */
  createSeminar: (data: SeminarFormData | FormData) =>
    api.post('/admin/seminars', data, {
      headers: data instanceof FormData ? { 'Content-Type': 'multipart/form-data' } : {},
    }),

  /** Update a seminar */
  updateSeminar: (id: number, data: SeminarFormData | FormData) =>
    api.post(`/admin/seminars/${id}`, data, {
      headers: data instanceof FormData ? { 'Content-Type': 'multipart/form-data' } : {},
      params: data instanceof FormData ? { _method: 'PUT' } : undefined,
    }),

  /** Delete a seminar */
  deleteSeminar: (id: number) =>
    api.delete(`/admin/seminars/${id}`),

  /** Get live push/pull URLs for a seminar */
  getSeminarLiveUrls: (id: number) =>
    api.get(`/admin/seminars/${id}/live-urls`),
}

export default adminApi
