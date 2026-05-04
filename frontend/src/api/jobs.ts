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
    api.get('/jobs', { params }),

  /** Single job detail */
  getJob: (id: number) =>
    api.get(`/jobs/${id}`),

  /** Submit application for a job */
  applyForJob: (jobId: number) =>
    api.post('/applications', { job_id: jobId }),

  /** My applications (authenticated student) */
  getMyApplications: (params?: ApplicationsParams) =>
    api.get('/applications', { params: { my: true, ...params } }),

  /** Withdraw an application */
  withdrawApplication: (id: number) =>
    api.delete(`/applications/${id}`),
}

export default jobsApi
