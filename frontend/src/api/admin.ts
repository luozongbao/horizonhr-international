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
}

export default adminApi
