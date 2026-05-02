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
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const initialised = ref(false)

  const isLoggedIn = computed(() => !!user.value)

  async function init() {
    if (token.value) {
      try {
        const { data } = await api.get('/user')
        user.value = data
      } catch {
        token.value = null
        localStorage.removeItem('auth_token')
      }
    }
    initialised.value = true
  }

  async function login(email: string, password: string) {
    const { data } = await api.post('/auth/login', { email, password })
    token.value = data.token
    user.value = data.user
    localStorage.setItem('auth_token', data.token)
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
    }
  }

  function redirectForRole() {
    if (!user.value) return { path: '/login' }
    const roleMap: Record<string, string> = {
      student: '/student/dashboard',
      enterprise: '/enterprise/dashboard',
      admin: '/admin/dashboard',
    }
    return { path: roleMap[user.value.role] ?? '/' }
  }

  return { user, token, initialised, isLoggedIn, init, login, logout, redirectForRole }
})
