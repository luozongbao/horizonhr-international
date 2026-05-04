/**
 * Student portal API calls.
 */
import api from './axios'

export interface UpdateProfilePayload {
  name?: string
  phone?: string
  date_of_birth?: string
  nationality?: string
  current_city?: string
  bio?: string
  prefer_lang?: 'en' | 'zh_cn' | 'th'
}

export interface ChangePasswordPayload {
  current_password: string
  password: string
  password_confirmation: string
}

export const studentApi = {
  /** Get current authenticated user + profile */
  getProfile: () =>
    api.get('/auth/me'),

  /** Update personal info */
  updateProfile: (data: UpdateProfilePayload) =>
    api.put('/users/profile', data),

  /** Upload avatar — send as FormData */
  uploadAvatar: (formData: FormData) =>
    api.post('/users/avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),

  /** Change password */
  changePassword: (data: ChangePasswordPayload) =>
    api.put('/auth/password/change', data),

  /** My resumes list */
  getResumes: () =>
    api.get('/resumes/my'),

  /** Upload resume — send as FormData */
  uploadResume: (formData: FormData, onProgress?: (pct: number) => void) =>
    api.post('/resumes', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (evt) => {
        if (onProgress && evt.total) {
          onProgress(Math.round((evt.loaded * 100) / evt.total))
        }
      },
    }),

  /** Delete a resume version */
  deleteResume: (id: number) =>
    api.delete(`/resumes/${id}`),

  /** My job applications (recent) */
  getApplications: (params?: { my?: boolean; per_page?: number }) =>
    api.get('/applications', { params: { my: true, ...params } }),

  /** My interviews list */
  getInterviews: (params?: { status?: string; per_page?: number }) =>
    api.get('/interviews', { params: { my: true, ...params } }),

  /** Single interview detail */
  getInterview: (id: number) =>
    api.get(`/interviews/${id}`),

  /** Join interview — get TRTC credentials */
  joinInterview: (id: number) =>
    api.post(`/interviews/${id}/join`),

  /** My seminar registrations */
  getSeminarRegistrations: (params?: { per_page?: number }) =>
    api.get('/seminar-registrations/my', { params }),

  /** Get interview chat messages */
  getInterviewMessages: (interviewId: number) =>
    api.get(`/interviews/${interviewId}/messages`),

  /** Send a chat message in the interview */
  sendInterviewMessage: (interviewId: number, content: string) =>
    api.post(`/interviews/${interviewId}/messages`, { content }),
}

export default studentApi
