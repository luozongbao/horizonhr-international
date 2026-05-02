<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

const form = ref({ email: '', password: '' })
const loading = ref(false)

async function handleLogin() {
  loading.value = true
  try {
    await auth.login(form.value.email, form.value.password)
    router.push(auth.redirectForRole())
  } catch (e: any) {
    ElMessage.error(e.response?.data?.message ?? t('common.error'))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-primary-light flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-md p-10 w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-primary">
          Hubei <span class="text-accent">Horizon</span> HR
        </h1>
        <p class="text-brand-secondary mt-2">Sign in to your account</p>
      </div>

      <el-form @submit.prevent="handleLogin" label-position="top">
        <el-form-item :label="t('auth.email')">
          <el-input v-model="form.email" type="email" size="large" autocomplete="email" />
        </el-form-item>
        <el-form-item :label="t('auth.password')">
          <el-input v-model="form.password" type="password" size="large" show-password autocomplete="current-password" />
        </el-form-item>
        <el-button
          type="primary"
          size="large"
          class="w-full mt-2"
          native-type="submit"
          :loading="loading"
        >
          {{ t('auth.loginBtn') }}
        </el-button>
      </el-form>

      <div class="text-center mt-6 text-sm text-brand-secondary">
        <router-link to="/register/student" class="text-primary hover:underline">Register as Student</router-link>
        &nbsp;·&nbsp;
        <router-link to="/register/enterprise" class="text-primary hover:underline">Register as Enterprise</router-link>
      </div>
    </div>
  </div>
</template>
