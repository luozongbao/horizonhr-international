<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()

onMounted(async () => {
  const token    = route.query.token as string
  const provider = route.query.provider as string

  if (!token) {
    router.replace('/login')
    return
  }

  // Store token from OAuth provider response
  // Full implementation in TASK-022 (auth views)
  localStorage.setItem('auth_token', token)
  await auth.fetchProfile()
  router.replace(auth.redirectForRole().path)
})
</script>

<template>
  <div class="flex items-center justify-center min-h-screen">
    <div class="text-center">
      <el-icon class="is-loading" size="40"><Loading /></el-icon>
      <p class="mt-4 text-gray-500">Completing sign-in…</p>
    </div>
  </div>
</template>
