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

export interface ResumeParams {
  search?: string
  nationality?: string
  education_level?: string
  availability?: string
  language?: string
  per_page?: number
  page?: number
}

export interface InterviewData {
  title?: string
  student_id?: number
  job_id?: number
  scheduled_at: string
  duration_minutes: number
  interviewer_name?: string
  notes?: string
}

export interface CompleteInterviewData {
  result: 'pass' | 'fail' | 'pending'
  result_notes?: string
}

export interface InterviewParams {
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
    api.post('/enterprise/profile/logo', formData, { headers: { 'Content-Type': 'multipart/form-data' } }),

  /** My job postings */
  getJobs: (params?: JobParams) =>
    api.get('/enterprise/jobs', { params }),

  /** Create job posting */
  createJob: (data: Partial<JobData>) =>
    api.post('/enterprise/jobs', data),

  /** Update job posting */
  updateJob: (id: number, data: Partial<JobData>) =>
    api.put(`/enterprise/jobs/${id}`, data),

  /** Delete job posting */
  deleteJob: (id: number) =>
    api.delete(`/enterprise/jobs/${id}`),

  /** Publish a job */
  publishJob: (id: number) =>
    api.put(`/enterprise/jobs/${id}/publish`),

  /** Close (unpublish) a job */
  unpublishJob: (id: number) =>
    api.put(`/enterprise/jobs/${id}/close`),

  /** Applications across enterprise's jobs */
  getApplications: (params?: ApplicationParams) =>
    api.get('/enterprise/applications', { params }),

  /** Applications for a specific job */
  getApplicationsForJob: (jobId: number, params?: { status?: string; per_page?: number; page?: number }) =>
    api.get(`/enterprise/jobs/${jobId}/applications`, { params }),

  /** Update application status */
  updateApplicationStatus: (appId: number, status: string) =>
    api.put(`/enterprise/applications/${appId}/status`, { status }),

  /** My interviews (enterprise view) */
  getInterviews: (params?: InterviewParams) =>
    api.get('/interviews', { params }),

  /** Single interview detail */
  getInterview: (id: number) =>
    api.get(`/interviews/${id}`),

  /** Schedule a new interview */
  scheduleInterview: (data: InterviewData) =>
    api.post('/interviews', data),

  /** Update interview (reschedule / update notes) */
  updateInterview: (id: number, data: Partial<InterviewData>) =>
    api.put(`/interviews/${id}`, data),

  /** Cancel an interview */
  cancelInterview: (id: number) =>
    api.put(`/interviews/${id}/cancel`),

  /** Join interview — get TRTC credentials */
  joinInterview: (id: number) =>
    api.post(`/interviews/${id}/join`),

  /** Mark interview as complete + set result */
  completeInterview: (id: number, data: CompleteInterviewData) =>
    api.put(`/interviews/${id}/complete`, data),

  /** Talent pool — approved resumes */
  getApprovedResumes: (params?: ResumeParams) =>
    api.get('/enterprise/talent', { params }),

  /** Resume detail (includes download_url) */
  getResume: (id: number) =>
    api.get(`/enterprise/talent/${id}`),

  /** Get interview chat messages */
  getInterviewMessages: (interviewId: number) =>
    api.get(`/interviews/${interviewId}/messages`),

  /** Send a chat message in the interview */
  sendInterviewMessage: (interviewId: number, content: string) =>
    api.post(`/interviews/${interviewId}/messages`, { content }),
}

export default enterpriseApi
