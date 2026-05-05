<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { authApi } from '@/api/auth'

const { t } = useI18n()
const route  = useRoute()
const router = useRouter()

const status = ref<'loading' | 'success' | 'error'>('loading')

onMounted(async () => {
  const token = (route.params.token as string) || (route.query.token as string)
  const email = route.query.email as string
  if (!token || !email) {
    status.value = 'error'
    return
  }
  try {
    await authApi.confirmEmail(token, email)
    status.value = 'success'
    setTimeout(() => router.push('/login'), 3000)
  } catch {
    status.value = 'error'
  }
})
</script>

<template>
  <div class="verify-shell">
    <div class="verify-card">
      <router-link to="/" class="card-logo">Hubei <span>Horizon</span> HR</router-link>

      <template v-if="status === 'loading'">
        <el-icon class="is-loading spin" size="48"><Loading /></el-icon>
        <p class="card-text">{{ t('auth.emailVerifying') }}</p>
      </template>

      <template v-else-if="status === 'success'">
        <div class="status-icon success">✅</div>
        <h2 class="card-title success">{{ t('auth.emailVerified') }}</h2>
        <p class="card-text">{{ t('auth.emailVerifyRedirect') }}</p>
        <router-link to="/login">
          <el-button type="primary" size="large" class="card-btn">{{ t('auth.backToLogin') }}</el-button>
        </router-link>
      </template>

      <template v-else>
        <div class="status-icon error">❌</div>
        <h2 class="card-title error">{{ t('auth.emailVerifyFailed') }}</h2>
        <router-link to="/login">
          <el-button type="default" size="large" class="card-btn">{{ t('auth.backToLogin') }}</el-button>
        </router-link>
      </template>
    </div>
  </div>
</template>

<style scoped>
.verify-shell {
  min-height: 100vh;
  background: #E6F0FF;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
}
.verify-card {
  background: #fff;
  border-radius: 16px;
  padding: 48px 40px;
  text-align: center;
  width: 100%;
  max-width: 420px;
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
.card-title { font-size: 20px; font-weight: 600; margin-bottom: 12px; }
.card-title.success { color: #28A745; }
.card-title.error { color: #DC3545; }
.card-text { font-size: 15px; color: #6C757D; margin-bottom: 24px; line-height: 1.6; }
.card-btn { width: 100%; font-weight: 600; }
.spin { color: #003366; margin-bottom: 16px; }
</style>
