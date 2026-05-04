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
    api.get('/pages/home'),

  getAboutPage: () =>
    api.get('/pages/about'),

  getStudyPage: () =>
    api.get('/pages/study-in-china'),

  getCorporatePage: () =>
    api.get('/pages/corporate'),

  getUniversities: (params?: UniversityParams) =>
    api.get('/universities', { params }),

  getUniversity: (id: number) =>
    api.get(`/universities/${id}`),

  getSeminars: (params?: SeminarParams) =>
    api.get('/seminars', { params }),

  getSeminar: (id: number) =>
    api.get(`/seminars/${id}`),

  getSeminarWatch: (id: number) =>
    api.get(`/seminars/${id}/watch`),

  registerSeminar: (id: number) =>
    api.post(`/seminars/${id}/register`),

  unregisterSeminar: (id: number) =>
    api.delete(`/seminars/${id}/unregister`),

  getSeminarDanmu: (id: number, afterId?: number) =>
    api.get(`/seminars/${id}/danmu`, { params: afterId ? { after: afterId } : {} }),

  sendSeminarDanmu: (id: number, message: string) =>
    api.post(`/seminars/${id}/danmu`, { message }),

  getPosts: (params?: PostParams) =>
    api.get('/posts', { params }),

  getPost: (slug: string) =>
    api.get(`/posts/${slug}`),

  getContactPage: () =>
    api.get('/pages/contact'),

  getResumes: (params?: ResumeParams) =>
    api.get('/resumes', { params }),

  getResume: (id: number) =>
    api.get(`/resumes/${id}`),

  submitContact: (data: ContactPayload) =>
    api.post('/contact', data),
}
