/**
 * Public (unauthenticated) API calls for home page, universities, seminars, and posts.
 */
import api from './axios'

export interface UniversityParams {
  per_page?: number
  page?: number
  search?: string
  region?: string
  program_type?: string
}

export interface SeminarParams {
  status?: string
  per_page?: number
  page?: number
}

export interface PostParams {
  per_page?: number
  page?: number
  category?: string
}

export interface ResumeParams {
  per_page?: number
  page?: number
  search?: string
  nationality?: string
  education_level?: string
  availability?: string
}

export interface ContactPayload {
  name: string
  email: string
  phone?: string
  company_name?: string
  subject: string
  message: string
}

export const publicApi = {
  getHomePage: () =>
    api.get('/public/pages/home'),

  getAboutPage: () =>
    api.get('/public/pages/about'),

  getStudyPage: () =>
    api.get('/public/pages/study-in-china'),

  getCorporatePage: () =>
    api.get('/public/pages/corporate'),

  getUniversities: (params?: UniversityParams) =>
    api.get('/public/universities', { params }),

  getUniversity: (id: number) =>
    api.get(`/public/universities/${id}`),

  getSeminars: (params?: SeminarParams) =>
    api.get('/public/seminars', { params }),

  getSeminar: (id: number) =>
    api.get(`/public/seminars/${id}`),

  getSeminarWatch: (id: number) =>
    api.get(`/public/seminars/${id}/watch`),

  registerSeminar: (id: number) =>
    api.post(`/public/seminars/${id}/register`),

  unregisterSeminar: (id: number) =>
    api.delete(`/public/seminars/${id}/unregister`),

  getSeminarDanmu: (id: number, afterId?: number) =>
    api.get(`/public/seminars/${id}/danmu`, { params: afterId ? { after: afterId } : {} }),

  sendSeminarDanmu: (id: number, message: string) =>
    api.post(`/public/seminars/${id}/danmu`, { message }),

  getPosts: (params?: PostParams) =>
    api.get('/public/posts', { params }),

  getPost: (slug: string) =>
    api.get(`/public/posts/${slug}`),

  getContactPage: () =>
    api.get('/public/pages/contact'),

  getResumes: (params?: ResumeParams) =>
    api.get('/public/talent', { params }),

  getResume: (id: number) =>
    api.get(`/public/talent/${id}`),

  submitContact: (data: ContactPayload) =>
    api.post('/public/contact', data),
}
