/**
 * Authentication API calls.
 * Full implementation in TASK-021; this module defines the interface.
 */
import api from './axios'

export interface LoginPayload {
  email: string
  password: string
}

export interface RegisterStudentPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  pdpa_consent: boolean
}

export interface RegisterEnterprisePayload {
  company_name: string
  contact_name: string
  email: string
  password: string
  password_confirmation: string
  pdpa_consent: boolean
}

export interface AuthResponse {
  token: string
  user: {
    id: number
    name: string
    email: string
    role: 'student' | 'enterprise' | 'admin'
    status: string
    language: string
  }
}

export const authApi = {
  login: (payload: LoginPayload) =>
    api.post<AuthResponse>('/auth/login', payload),

  logout: () =>
    api.post('/auth/logout'),

  me: () =>
    api.get('/user'),

  registerStudent: (payload: RegisterStudentPayload) =>
    api.post<AuthResponse>('/auth/register/student', payload),

  registerEnterprise: (payload: RegisterEnterprisePayload) =>
    api.post<AuthResponse>('/auth/register/enterprise', payload),

  forgotPassword: (email: string) =>
    api.post('/auth/forgot-password', { email }),

  resetPassword: (payload: { token: string; email: string; password: string; password_confirmation: string }) =>
    api.post('/auth/reset-password', payload),

  confirmEmail: (token: string) =>
    api.get(`/auth/email/confirm/${token}`),
}

export default authApi
