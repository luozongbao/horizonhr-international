import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api'

interface User {
  id: number
  name: string
  email: string
  role: 'student' | 'enterprise' | 'admin'
  status: string
  language: string
  avatar_url?: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(JSON.parse(localStorage.getItem('auth_user') ?? 'null'))
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const initialised = ref(false)

  const isLoggedIn  = computed(() => !!user.value && !!token.value)
  const isAdmin     = computed(() => user.value?.role === 'admin')
  const isStudent   = computed(() => user.value?.role === 'student')
  const isEnterprise = computed(() => user.value?.role === 'enterprise')

  /** Persist token + user to localStorage and store */
  function setAuth(newToken: string, newUser: User) {
    token.value = newToken
    user.value  = newUser
    localStorage.setItem('auth_token', newToken)
    localStorage.setItem('auth_user', JSON.stringify(newUser))
  }

  /** Wipe token + user from memory and localStorage */
  function clearAuth() {
    token.value = null
    user.value  = null
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
  }

  /** Verify stored token and hydrate user from backend */
  async function fetchProfile() {
    try {
      const { data } = await api.get('/auth/me')
      user.value = data.data ?? data
      localStorage.setItem('auth_user', JSON.stringify(user.value))
    } catch {
      clearAuth()
    }
  }

  /** Called once on first router navigation — re-hydrates auth from stored token */
  async function init() {
    if (token.value) {
      await fetchProfile()
    }
    initialised.value = true
  }

  async function login(email: string, password: string) {
    const { data } = await api.post('/auth/login', { email, password })
    setAuth(data.token, data.user)
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      clearAuth()
    }
  }

  function redirectForRole() {
    if (!user.value) return { path: '/login' }
    const roleMap: Record<string, string> = {
      student:    '/student/dashboard',
      enterprise: '/enterprise/dashboard',
      admin:      '/admin/dashboard',
    }
    return { path: roleMap[user.value.role] ?? '/' }
  }

  return {
    user, token, initialised,
    isLoggedIn, isAdmin, isStudent, isEnterprise,
    setAuth, clearAuth, fetchProfile, init, login, logout, redirectForRole,
  }
})

