/**
 * Enterprise portal API calls.
 */
import api from './axios'

// ─── Interfaces ───────────────────────────────────────────────────────────────

export interface EnterpriseProfileData {
  company_name: string
  industry?: string
  company_size?: string
  website?: string
  contact_email?: string
  contact_phone?: string
  founded_year?: number | null
  description_en?: string
  description_zh_cn?: string
  description_th?: string
  office_locations?: string
  contact_name?: string
  contact_position?: string
  logo_url?: string
  status?: string
}

export interface JobData {
  title_en: string
  title_zh_cn?: string
  title_th?: string
  job_type: string
  location?: string
  salary_min?: number | null
  salary_max?: number | null
  salary_currency?: string
  salary_not_disclosed?: boolean
  description_en?: string
  description_zh_cn?: string
  description_th?: string
  requirements_en?: string
  requirements_zh_cn?: string
  requirements_th?: string
  deadline?: string | null
  industry?: string
  status?: 'draft' | 'published' | 'closed'
}

export interface JobParams {
  status?: string
  per_page?: number
  page?: number
}

export interface ApplicationParams {
  job_id?: number
  status?: string
  per_page?: number
  page?: number
}

// ─── API module ───────────────────────────────────────────────────────────────

export const enterpriseApi = {
  /** Dashboard summary stats */
  getDashboard: () =>
    api.get('/enterprise/dashboard'),

  /** Get company profile */
  getProfile: () =>
    api.get('/enterprise/profile'),

  /** Update company profile */
  updateProfile: (data: Partial<EnterpriseProfileData>) =>
    api.put('/enterprise/profile', data),

  /** Upload company logo */
  uploadLogo: (formData: FormData) =>
    api.post('/enterprise/logo', formData, { headers: { 'Content-Type': 'multipart/form-data' } }),

  /** My job postings */
  getJobs: (params?: JobParams) =>
    api.get('/jobs', { params: { my: true, ...params } }),

  /** Create job posting */
  createJob: (data: Partial<JobData>) =>
    api.post('/jobs', data),

  /** Update job posting */
  updateJob: (id: number, data: Partial<JobData>) =>
    api.put(`/jobs/${id}`, data),

  /** Delete job posting */
  deleteJob: (id: number) =>
    api.delete(`/jobs/${id}`),

  /** Publish a job */
  publishJob: (id: number) =>
    api.put(`/jobs/${id}/publish`),

  /** Unpublish a job */
  unpublishJob: (id: number) =>
    api.put(`/jobs/${id}/unpublish`),

  /** Applications across enterprise's jobs */
  getApplications: (params?: ApplicationParams) =>
    api.get('/applications', { params }),

  /** Applications for a specific job */
  getApplicationsForJob: (jobId: number, params?: { status?: string; per_page?: number; page?: number }) =>
    api.get('/applications', { params: { job_id: jobId, ...params } }),

  /** Update application status */
  updateApplicationStatus: (appId: number, status: string) =>
    api.put(`/applications/${appId}/status`, { status }),

  /** My interviews (enterprise view) */
  getInterviews: (params?: { status?: string; per_page?: number }) =>
    api.get('/interviews', { params }),
}

export default enterpriseApi
