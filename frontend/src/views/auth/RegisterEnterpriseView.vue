<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { type FormInstance, type FormRules } from 'element-plus'
import { authApi } from '@/api/auth'
import LanguageSwitcher from '@/components/common/LanguageSwitcher.vue'
import SocialLoginButtons from '@/components/auth/SocialLoginButtons.vue'

const { t } = useI18n()

const formRef = ref<FormInstance>()
const loading = ref(false)
const submitted = ref(false)

const form = reactive({
  company_name: '',
  contact_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  industry: '',
  company_size: '',
  description: '',
  pdpa_consent: false,
})

const validatePasswordMatch = (_rule: any, value: string, callback: any) => {
  if (value !== form.password) {
    callback(new Error(t('validation.passwordMismatch')))
  } else {
    callback()
  }
}

const rules = computed<FormRules>(() => ({
  company_name: [{ required: true, message: t('validation.companyNameRequired'), trigger: 'blur' }],
  contact_name: [{ required: true, message: t('validation.contactNameRequired'), trigger: 'blur' }],
  email: [
    { required: true, message: t('validation.emailRequired'), trigger: 'blur' },
    { type: 'email', message: t('validation.emailInvalid'), trigger: 'blur' },
  ],
  password: [
    { required: true, message: t('validation.passwordRequired'), trigger: 'blur' },
    { min: 8, message: t('validation.passwordMin'), trigger: 'blur' },
  ],
  password_confirmation: [
    { required: true, message: t('validation.passwordConfirmRequired'), trigger: 'blur' },
    { validator: validatePasswordMatch, trigger: 'blur' },
  ],
  pdpa_consent: [
    {
      validator: (_r: any, v: boolean, cb: any) =>
        v ? cb() : cb(new Error(t('validation.agreeRequired'))),
      trigger: 'change',
    },
  ],
}))

async function handleSubmit() {
  if (!formRef.value) return
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return

  loading.value = true
  try {
    await authApi.registerEnterprise({
      company_name: form.company_name,
      contact_name: form.contact_name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      industry: form.industry,
      company_size: form.company_size,
      description: form.description,
      pdpa_consent: form.pdpa_consent,
    })
    submitted.value = true
  } finally {
    loading.value = false
  }
}

const industries = [
  'Technology', 'Manufacturing', 'Finance & Banking', 'Education',
  'Healthcare', 'Retail & E-commerce', 'Logistics & Supply Chain',
  'Real Estate', 'Energy & Resources', 'Consulting', 'Media & Entertainment', 'Other',
]

const companySizes = [
  { label: '1–50 employees',    value: '1-50' },
  { label: '51–200 employees',  value: '51-200' },
  { label: '201–500 employees', value: '201-500' },
  { label: '500+ employees',    value: '500+' },
]
</script>

<template>
  <div class="auth-container">
    <aside class="auth-left">
      <div class="left-top">
        <div class="left-logo">HBHR <span>Portal</span></div>
        <LanguageSwitcher dark />
      </div>
      <h1 class="left-title">Find Top Southeast Asian Talent</h1>
      <p class="left-sub">Register your enterprise to access a curated talent pool of students and graduates from Southeast Asia.</p>
      <div class="features">
        <div class="feature">
          <div class="feature-icon">👥</div>
          <div><p class="feature-title">Talent Pool</p><p class="feature-desc">Access vetted student profiles</p></div>
        </div>
        <div class="feature">
          <div class="feature-icon">🎥</div>
          <div><p class="feature-title">Video Interviews</p><p class="feature-desc">TRTC-powered online interviews</p></div>
        </div>
        <div class="feature">
          <div class="feature-icon">📢</div>
          <div><p class="feature-title">Job Postings</p><p class="feature-desc">Reach thousands of candidates</p></div>
        </div>
      </div>
    </aside>

    <section class="auth-right">
      <div class="form-wrap">
        <div class="auth-tabs">
          <router-link to="/login" class="auth-tab">{{ t('auth.login') }}</router-link>
          <router-link to="/register/enterprise" class="auth-tab active">{{ t('auth.register') }}</router-link>
        </div>

        <template v-if="submitted">
          <div class="success-card">
            <div class="success-icon">🏢</div>
            <h2>Registration Submitted!</h2>
            <p>{{ t('auth.pendingApproval') }}</p>
            <router-link to="/login">
              <el-button type="primary" size="large" class="submit-btn mt-4">{{ t('auth.backToLogin') }}</el-button>
            </router-link>
          </div>
        </template>

        <template v-else>
          <h2 class="form-title">{{ t('auth.registerEnterprise') }}</h2>
          <p class="form-sub">
            {{ t('auth.alreadyHaveAccount') }}
            <router-link to="/login">{{ t('auth.login') }}</router-link>
          </p>

          <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
            <div class="form-grid">
              <el-form-item :label="t('auth.companyName')" prop="company_name" class="full">
                <el-input v-model="form.company_name" size="large" :placeholder="t('auth.companyName')" />
              </el-form-item>
              <el-form-item :label="t('auth.contactName')" prop="contact_name" class="full">
                <el-input v-model="form.contact_name" size="large" :placeholder="t('auth.contactName')" />
              </el-form-item>
              <el-form-item :label="t('auth.email')" prop="email" class="full">
                <el-input v-model="form.email" type="email" size="large" autocomplete="email" :placeholder="t('auth.email')" />
              </el-form-item>
              <el-form-item :label="t('auth.password')" prop="password">
                <el-input v-model="form.password" type="password" size="large" show-password autocomplete="new-password" />
              </el-form-item>
              <el-form-item :label="t('auth.confirmPassword')" prop="password_confirmation">
                <el-input v-model="form.password_confirmation" type="password" size="large" show-password autocomplete="new-password" />
              </el-form-item>
              <el-form-item :label="t('auth.industry')" prop="industry">
                <el-select v-model="form.industry" size="large" :placeholder="t('auth.industry')" style="width:100%" filterable>
                  <el-option v-for="i in industries" :key="i" :label="i" :value="i" />
                </el-select>
              </el-form-item>
              <el-form-item :label="t('auth.companySize')" prop="company_size">
                <el-select v-model="form.company_size" size="large" :placeholder="t('auth.companySize')" style="width:100%">
                  <el-option v-for="s in companySizes" :key="s.value" :label="s.label" :value="s.value" />
                </el-select>
              </el-form-item>
              <el-form-item :label="t('auth.description')" prop="description" class="full">
                <el-input
                  v-model="form.description"
                  type="textarea"
                  :rows="3"
                  :placeholder="t('auth.description')"
                />
              </el-form-item>
              <el-form-item prop="pdpa_consent" class="full">
                <el-checkbox v-model="form.pdpa_consent">
                  {{ t('auth.pdpaConsent') }}
                  <router-link to="/pages/privacy-policy" target="_blank" class="terms-link">Privacy Policy</router-link>
                </el-checkbox>
              </el-form-item>
            </div>
            <el-button type="primary" size="large" class="submit-btn" :loading="loading" @click="handleSubmit">
              {{ t('auth.registerEnterprise') }}
            </el-button>
          </el-form>

          <SocialLoginButtons />

          <p class="switch-link">
            Are you a student?
            <router-link to="/register/student">{{ t('auth.switchToStudent') }}</router-link>
          </p>
        </template>
      </div>
    </section>
  </div>
</template>

<style scoped>
.auth-container { display: flex; min-height: 100vh; }
.auth-left {
  width: 45%; background: linear-gradient(135deg, #003366 0%, #004080 100%);
  color: #fff; padding: 48px 56px; display: flex; flex-direction: column;
}
.left-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 48px; }
.left-logo { font-size: 28px; font-weight: 700; }
.left-logo span { color: #FF6B35; }
.left-title { font-size: 28px; font-weight: 700; line-height: 1.3; margin-bottom: 16px; }
.left-sub { font-size: 15px; opacity: 0.85; margin-bottom: 40px; line-height: 1.6; }
.features { display: flex; flex-direction: column; gap: 20px; margin-top: auto; }
.feature { display: flex; gap: 16px; align-items: flex-start; }
.feature-icon { width: 44px; height: 44px; background: rgba(255,255,255,0.12); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.feature-title { font-size: 15px; font-weight: 600; margin-bottom: 2px; }
.feature-desc { font-size: 12px; opacity: 0.7; }
.auth-right { flex: 1; display: flex; align-items: flex-start; justify-content: center; padding: 40px 56px; background: #fff; overflow-y: auto; }
.form-wrap { width: 100%; max-width: 480px; }
.auth-tabs { display: flex; border-bottom: 1px solid #DEE2E6; margin-bottom: 28px; }
.auth-tab { padding: 10px 24px; font-size: 14px; font-weight: 500; color: #6C757D; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; }
.auth-tab:hover { color: #003366; }
.auth-tab.active { color: #003366; border-bottom-color: #003366; }
.form-title { font-size: 24px; font-weight: 600; color: #1A1A2E; margin-bottom: 6px; }
.form-sub { font-size: 14px; color: #6C757D; margin-bottom: 20px; }
.form-sub a { color: #FF6B35; text-decoration: none; font-weight: 500; }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0 16px; }
.form-grid .full { grid-column: span 2; }
.terms-link { color: #FF6B35; text-decoration: none; margin-left: 4px; }
.submit-btn { width: 100%; font-size: 15px; font-weight: 600; margin-top: 8px; }
.mt-4 { margin-top: 16px; }
.switch-link { text-align: center; font-size: 13px; color: #6C757D; margin-top: 16px; }
.switch-link a { color: #FF6B35; text-decoration: none; font-weight: 500; }
.success-card { text-align: center; padding: 40px 20px; }
.success-icon { font-size: 56px; margin-bottom: 16px; }
.success-card h2 { font-size: 22px; font-weight: 600; color: #003366; margin-bottom: 12px; }
.success-card p { font-size: 15px; color: #6C757D; line-height: 1.6; }
@media (max-width: 768px) {
  .auth-left { display: none; }
  .auth-right { padding: 32px 24px; min-height: 100vh; }
  .form-grid { grid-template-columns: 1fr; }
  .form-grid .full { grid-column: span 1; }
}
</style>

