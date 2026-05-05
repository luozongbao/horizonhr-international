/**
 * Jobs and Applications API calls.
 */
import api from './axios'

export interface JobsParams {
  search?: string
  location?: string
  job_type?: string
  industry?: string
  per_page?: number
  page?: number
}

export interface ApplicationsParams {
  status?: string
  per_page?: number
  page?: number
}

export const jobsApi = {
  /** List published jobs */
  getJobs: (params?: JobsParams) =>
    api.get('/public/jobs', { params }),

  /** Single job detail */
  getJob: (id: number) =>
    api.get(`/public/jobs/${id}`),

  /** Submit application for a job */
  applyForJob: (jobId: number) =>
    api.post('/student/applications', { job_id: jobId }),

  /** My applications (authenticated student) */
  getMyApplications: (params?: ApplicationsParams) =>
    api.get('/student/applications', { params }),

  /** Withdraw an application */
  withdrawApplication: (id: number) =>
    api.delete(`/student/applications/${id}`),
}

export default jobsApi
