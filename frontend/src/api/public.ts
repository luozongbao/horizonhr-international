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

export const publicApi = {
  getHomePage: () =>
    api.get('/pages/home'),

  getAboutPage: () =>
    api.get('/pages/about'),

  getStudyPage: () =>
    api.get('/pages/study-in-china'),

  getUniversities: (params?: UniversityParams) =>
    api.get('/universities', { params }),

  getUniversity: (id: number) =>
    api.get(`/universities/${id}`),

  getSeminars: (params?: SeminarParams) =>
    api.get('/seminars', { params }),

  getPosts: (params?: PostParams) =>
    api.get('/posts', { params }),
}
