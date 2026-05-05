<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ElMessage, type FormInstance, type FormRules } from 'element-plus'
import { useAuthStore } from '@/stores/auth'
import { authApi } from '@/api/auth'
import LanguageSwitcher from '@/components/common/LanguageSwitcher.vue'
import SocialLoginButtons from '@/components/auth/SocialLoginButtons.vue'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

const formRef = ref<FormInstance>()
const loading = ref(false)
const errorMsg = ref('')

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const rules = reactive<FormRules>({
  email: [
    { required: true, message: () => t('validation.emailRequired'), trigger: 'blur' },
    { type: 'email', message: () => t('validation.emailInvalid'), trigger: 'blur' },
  ],
  password: [
    { required: true, message: () => t('validation.passwordRequired'), trigger: 'blur' },
    { min: 8, message: () => t('validation.passwordMin'), trigger: 'blur' },
  ],
})

async function handleLogin() {
  if (!formRef.value) return
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return

  errorMsg.value = ''
  loading.value = true
  try {
    const { data } = await authApi.login({ email: form.email, password: form.password })
    auth.setAuth(data.data.token, data.data.user)
    await router.push(auth.redirectForRole())
  } catch (e: any) {
    errorMsg.value =
      e.response?.data?.error?.message ??
      e.response?.data?.message ??
      t('common.error')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-container">
    <!-- Left branding panel -->
    <aside class="auth-left">
      <div class="left-top">
        <div class="left-logo">HBHR <span>Portal</span></div>
        <LanguageSwitcher class="left-lang" dark />
      </div>
      <h1 class="left-title">Connect with Top Talent Across Southeast Asia</h1>
      <p class="left-sub">Join thousands of students and enterprises on the premier platform for studying in China and recruiting international talent.</p>
      <div class="features">
        <div class="feature">
          <div class="feature-icon">🎓</div>
          <div>
            <p class="feature-title">Study in China</p>
            <p class="feature-desc">Access 50+ partner universities across China</p>
          </div>
        </div>
        <div class="feature">
          <div class="feature-icon">💼</div>
          <div>
            <p class="feature-title">Career Opportunities</p>
            <p class="feature-desc">Connect with top Chinese enterprises</p>
          </div>
        </div>
        <div class="feature">
          <div class="feature-icon">🎥</div>
          <div>
            <p class="feature-title">Online Interviews</p>
            <p class="feature-desc">WebRTC-powered video interviews</p>
          </div>
        </div>
        <div class="feature">
          <div class="feature-icon">📚</div>
          <div>
            <p class="feature-title">Live Seminars</p>
            <p class="feature-desc">Webinars with industry experts</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Right form panel -->
    <section class="auth-right">
      <div class="form-wrap">
        <!-- Tabs -->
        <div class="auth-tabs">
          <router-link to="/login" class="auth-tab active">{{ t('auth.login') }}</router-link>
          <router-link to="/register/student" class="auth-tab">{{ t('auth.register') }}</router-link>
        </div>

        <h2 class="form-title">{{ t('auth.loginTitle') }}</h2>
        <p class="form-sub">
          {{ t('auth.noAccount') }}
          <router-link to="/register/student">{{ t('auth.register') }}</router-link>
        </p>

        <!-- Error alert -->
        <el-alert
          v-if="errorMsg"
          :title="errorMsg"
          type="error"
          show-icon
          :closable="true"
          @close="errorMsg = ''"
          class="mb-4"
        />

        <el-form ref="formRef" :model="form" :rules="rules" label-position="top" @submit.prevent="handleLogin">
          <el-form-item :label="t('auth.email')" prop="email">
            <el-input
              v-model="form.email"
              type="email"
              size="large"
              autocomplete="email"
              :placeholder="t('auth.email')"
            />
          </el-form-item>
          <el-form-item :label="t('auth.password')" prop="password">
            <el-input
              v-model="form.password"
              type="password"
              size="large"
              show-password
              autocomplete="current-password"
              :placeholder="t('auth.password')"
            />
          </el-form-item>

          <div class="form-row">
            <el-checkbox v-model="form.remember">{{ t('auth.rememberMe') }}</el-checkbox>
            <router-link to="/password/forgot" class="forgot-link">{{ t('auth.forgotPassword') }}</router-link>
          </div>

          <el-button
            type="primary"
            size="large"
            class="submit-btn"
            native-type="submit"
            :loading="loading"
            @click="handleLogin"
          >
            {{ t('auth.loginBtn') }}
          </el-button>
        </el-form>

        <SocialLoginButtons />
      </div>
    </section>
  </div>
</template>

<style scoped>
.auth-container {
  display: flex;
  min-height: 100vh;
}

/* Left panel */
.auth-left {
  width: 45%;
  background: linear-gradient(135deg, #003366 0%, #004080 100%);
  color: #fff;
  padding: 48px 56px;
  display: flex;
  flex-direction: column;
  gap: 0;
}
.left-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 48px;
}
.left-logo {
  font-size: 28px;
  font-weight: 700;
}
.left-logo span { color: #FF6B35; }
.left-title {
  font-size: 30px;
  font-weight: 700;
  line-height: 1.3;
  margin-bottom: 16px;
}
.left-sub {
  font-size: 16px;
  opacity: 0.85;
  margin-bottom: 40px;
  line-height: 1.6;
}
.features {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: auto;
}
.feature {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}
.feature-icon {
  width: 44px;
  height: 44px;
  background: rgba(255,255,255,0.12);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.feature-title {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 2px;
}
.feature-desc {
  font-size: 12px;
  opacity: 0.7;
}

/* Right panel */
.auth-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 56px;
  background: #fff;
  overflow-y: auto;
}
.form-wrap {
  width: 100%;
  max-width: 400px;
}

.auth-tabs {
  display: flex;
  border-bottom: 1px solid #DEE2E6;
  margin-bottom: 28px;
}
.auth-tab {
  padding: 10px 24px;
  font-size: 14px;
  font-weight: 500;
  color: #6C757D;
  text-decoration: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  transition: color 0.2s;
}
.auth-tab:hover { color: #003366; }
.auth-tab.active {
  color: #003366;
  border-bottom-color: #003366;
}

.form-title {
  font-size: 24px;
  font-weight: 600;
  color: #1A1A2E;
  margin-bottom: 6px;
}
.form-sub {
  font-size: 14px;
  color: #6C757D;
  margin-bottom: 24px;
}
.form-sub a {
  color: #FF6B35;
  text-decoration: none;
  font-weight: 500;
}

.form-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.forgot-link {
  font-size: 13px;
  color: #FF6B35;
  text-decoration: none;
}
.forgot-link:hover { text-decoration: underline; }

.submit-btn {
  width: 100%;
  font-size: 15px;
  font-weight: 600;
  margin-top: 4px;
}

.mb-4 { margin-bottom: 16px; }

@media (max-width: 768px) {
  .auth-left { display: none; }
  .auth-right { padding: 32px 24px; min-height: 100vh; }
}
</style>
