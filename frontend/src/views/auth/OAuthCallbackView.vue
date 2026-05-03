<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const auth   = useAuthStore()

const failed = ref(false)

onMounted(async () => {
  // Backend redirects to /oauth/callback#token=xxx  or  /oauth/callback?token=xxx
  const hash  = window.location.hash.slice(1)   // remove leading '#'
  const search = window.location.search.slice(1) // remove leading '?'

  const hashParams  = new URLSearchParams(hash)
  const queryParams = new URLSearchParams(search)

  const token = hashParams.get('token') ?? queryParams.get('token')
  const error = hashParams.get('error') ?? queryParams.get('error')

  if (error || !token) {
    failed.value = true
    return
  }

  try {
    localStorage.setItem('auth_token', token)
    await auth.fetchProfile()
    router.replace(auth.redirectForRole())
  } catch {
    failed.value = true
  }
})
</script>

<template>
  <div class="oauth-shell">
    <div class="oauth-card">
      <router-link to="/" class="card-logo">Hubei <span>Horizon</span> HR</router-link>

      <template v-if="!failed">
        <el-icon class="is-loading spin" size="48"><Loading /></el-icon>
        <p class="card-text">{{ t('auth.oauthProcessing') }}</p>
      </template>

      <template v-else>
        <div class="status-icon">❌</div>
        <p class="card-text">{{ t('auth.oauthError') }}</p>
        <router-link to="/login">
          <el-button type="primary" size="large" class="card-btn">{{ t('auth.backToLogin') }}</el-button>
        </router-link>
      </template>
    </div>
  </div>
</template>

<style scoped>
.oauth-shell {
  min-height: 100vh;
  background: #E6F0FF;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
}
.oauth-card {
  background: #fff;
  border-radius: 16px;
  padding: 48px 40px;
  text-align: center;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 4px 24px rgba(0,51,102,0.08);
}
.card-logo {
  display: inline-block;
  font-size: 22px;
  font-weight: 700;
  color: #003366;
  text-decoration: none;
  margin-bottom: 32px;
}
.card-logo span { color: #FF6B35; }
.status-icon { font-size: 56px; margin-bottom: 16px; }
.card-text { font-size: 15px; color: #6C757D; margin-bottom: 24px; }
.card-btn { width: 100%; font-weight: 600; }
.spin { color: #003366; margin-bottom: 16px; }
</style>

