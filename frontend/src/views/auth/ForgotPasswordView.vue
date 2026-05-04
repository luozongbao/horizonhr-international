<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { type FormInstance, type FormRules } from 'element-plus'
import { authApi } from '@/api/auth'

const { t } = useI18n()

const formRef  = ref<FormInstance>()
const loading  = ref(false)
const submitted = ref(false)

const form = reactive({ email: '' })

const rules = computed<FormRules>(() => ({
  email: [
    { required: true, message: t('validation.emailRequired'), trigger: 'blur' },
    { type: 'email', message: t('validation.emailInvalid'), trigger: 'blur' },
  ],
}))

async function handleSubmit() {
  if (!formRef.value) return
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return

  loading.value = true
  try {
    await authApi.forgotPassword(form.email)
  } catch {
    // always show success to prevent email enumeration
  } finally {
    loading.value = false
    submitted.value = true
  }
}
</script>

<template>
  <div class="simple-shell">
    <div class="simple-card">
      <router-link to="/" class="card-logo">Hubei <span>Horizon</span> HR</router-link>

      <template v-if="!submitted">
        <h2 class="card-title">{{ t('auth.forgotTitle') }}</h2>
        <p class="card-sub">{{ t('auth.forgotSubtitle') }}</p>

        <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
          <el-form-item :label="t('auth.email')" prop="email">
            <el-input v-model="form.email" type="email" size="large" autocomplete="email" :placeholder="t('auth.email')" />
          </el-form-item>
          <el-button type="primary" size="large" class="card-btn" :loading="loading" @click="handleSubmit">
            {{ t('auth.sendResetLink') }}
          </el-button>
        </el-form>
      </template>

      <template v-else>
        <div class="status-icon">📧</div>
        <p class="card-sub">{{ t('auth.resetLinkSent') }}</p>
      </template>

      <router-link to="/login" class="back-link">← {{ t('auth.backToLogin') }}</router-link>
    </div>
  </div>
</template>

<style scoped>
.simple-shell {
  min-height: 100vh;
  background: #E6F0FF;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
}
.simple-card {
  background: #fff;
  border-radius: 16px;
  padding: 48px 40px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 4px 24px rgba(0,51,102,0.08);
  text-align: center;
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
.card-title { font-size: 22px; font-weight: 600; color: #1A1A2E; margin-bottom: 8px; }
.card-sub { font-size: 14px; color: #6C757D; margin-bottom: 24px; line-height: 1.6; }
.status-icon { font-size: 56px; margin-bottom: 16px; }
.card-btn { width: 100%; font-weight: 600; }
.back-link { display: block; margin-top: 20px; color: #6C757D; font-size: 13px; text-decoration: none; }
.back-link:hover { color: #003366; }
</style>
