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

export interface PostParams {
  search?: string
  status?: string
  per_page?: number
  page?: number
}

export interface PostFormData {
  title_en: string
  title_zh_cn?: string
  title_th?: string
  slug?: string
  category?: string
  content_en?: string
  content_zh_cn?: string
  content_th?: string
  published_at?: string
  status?: 'draft' | 'published'
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
    api.put(`/admin/users/${id}/activate-enterprise`),

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

  /** Approve a resume */
  approveResume: (id: number) =>
    api.put(`/admin/resumes/${id}/review`, { status: 'approved' }),

  /** Reject a resume with notes */
  rejectResume: (id: number, reason: string) =>
    api.put(`/admin/resumes/${id}/review`, { status: 'rejected', notes: reason }),

  // ─── Interviews ────────────────────────────────────────────────────────────

  /** List all interviews (admin) */
  getInterviews: (params?: InterviewParams) =>
    api.get('/admin/interviews', { params }),

  /** Cancel an interview (uses shared interview route) */
  cancelInterview: (id: number) =>
    api.put(`/interviews/${id}/cancel`),

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

  // ─── CMS Pages ─────────────────────────────────────────────────────────────

  /** List all CMS pages */
  getPages: () =>
    api.get('/admin/pages'),

  /** Get a single CMS page with content */
  getPage: (id: number) =>
    api.get(`/admin/pages/${id}`),

  /** Update CMS page content */
  updatePage: (id: number, data: Record<string, unknown>) =>
    api.put(`/admin/pages/${id}`, data),

  // ─── Posts ─────────────────────────────────────────────────────────────────

  /** List all news posts */
  getPosts: (params?: PostParams) =>
    api.get('/admin/posts', { params }),

  /** Create a post (with optional thumbnail FormData) */
  createPost: (data: PostFormData | FormData) =>
    api.post('/admin/posts', data, {
      headers: data instanceof FormData ? { 'Content-Type': 'multipart/form-data' } : {},
    }),

  /** Update a post */
  updatePost: (id: number, data: PostFormData | FormData) =>
    api.post(`/admin/posts/${id}`, data, {
      headers: data instanceof FormData ? { 'Content-Type': 'multipart/form-data' } : {},
      params: data instanceof FormData ? { _method: 'PUT' } : undefined,
    }),

  /** Delete a post */
  deletePost: (id: number) =>
    api.delete(`/admin/posts/${id}`),

  /** Publish a post */
  publishPost: (id: number) =>
    api.put(`/admin/posts/${id}/publish`),

  /** Unpublish a post (revert to draft) */
  unpublishPost: (id: number) =>
    api.put(`/admin/posts/${id}/unpublish`),

  /** Upload media for rich text editor */
  uploadMedia: (formData: FormData) =>
    api.post('/admin/posts/media-upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),

  /** Get all settings */
  getSettings: () =>
    api.get('/admin/settings'),

  /** Update settings (bulk key-value map) */
  updateSettings: (data: Record<string, unknown>) =>
    api.put('/admin/settings', data),

  /** Upload site logo */
  uploadLogo: (formData: FormData) =>
    api.post('/admin/settings/upload-logo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),

  /** Upload favicon */
  uploadFavicon: (formData: FormData) =>
    api.post('/admin/settings/upload-favicon', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),

  /** Test SMTP connection */
  testSmtp: () =>
    api.post('/admin/settings/test-smtp'),

  // ─── Languages ─────────────────────────────────────────────────────────────

  /** List all languages */
  getLanguages: () =>
    api.get('/admin/languages'),

  /** Update a language (enable/disable, set default) */
  updateLanguage: (id: number, data: { is_enabled?: boolean; is_default?: boolean }) =>
    api.put(`/admin/languages/${id}`, data),

  /** Get translation keys for a given language code */
  getTranslations: (lang: string) =>
    api.get('/admin/translations', { params: { lang } }),

  /** Update translation keys */
  updateTranslations: (data: { lang: string; translations: Record<string, string> }) =>
    api.put('/admin/translations', data),

  /** Import translations from a JSON file */
  importTranslations: (lang: string, formData: FormData) =>
    api.post('/admin/translations/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      params: { lang },
    }),

  /** Export translations as a JSON download (returns blob) */
  exportTranslations: (lang: string) =>
    api.get('/admin/translations/export', {
      params: { lang },
      responseType: 'blob',
    }),
}

export default adminApi
