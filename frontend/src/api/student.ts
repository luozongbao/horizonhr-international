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
    api.put('/student/profile', data),

  /** Upload avatar — send as FormData */
  uploadAvatar: (formData: FormData) =>
    api.post('/student/profile/avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),

  /** Change password */
  changePassword: (data: ChangePasswordPayload) =>
    api.put('/student/profile/password', data),

  /** My resumes list */
  getResumes: () =>
    api.get('/student/resumes'),

  /** Upload resume — send as FormData */
  uploadResume: (formData: FormData, onProgress?: (pct: number) => void) =>
    api.post('/student/resumes', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (evt) => {
        if (onProgress && evt.total) {
          onProgress(Math.round((evt.loaded * 100) / evt.total))
        }
      },
    }),

  /** Delete a resume version */
  deleteResume: (id: number) =>
    api.delete(`/student/resumes/${id}`),

  /** My job applications (recent) */
  getApplications: (params?: { my?: boolean; per_page?: number }) =>
    api.get('/student/applications', { params }),

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
    api.get('/student/seminar-registrations', { params }),

  /** Get interview chat messages */
  getInterviewMessages: (interviewId: number) =>
    api.get(`/interviews/${interviewId}/messages`),

  /** Send a chat message in the interview */
  sendInterviewMessage: (interviewId: number, content: string) =>
    api.post(`/interviews/${interviewId}/messages`, { content }),
}

export default studentApi
