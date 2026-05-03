<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useSettingsStore } from '@/stores/settings'
import { useAuthStore } from '@/stores/auth'
import { useLanguageStore } from '@/stores/language'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import StudentLayout from '@/layouts/StudentLayout.vue'
import EnterpriseLayout from '@/layouts/EnterpriseLayout.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Element Plus locale objects
import epEn from 'element-plus/es/locale/lang/en'
import epZhCn from 'element-plus/es/locale/lang/zh-cn'
import epTh from 'element-plus/es/locale/lang/th'

const route = useRoute()
const { locale } = useI18n()
const settingsStore = useSettingsStore()
const authStore = useAuthStore()
const languageStore = useLanguageStore()

// Pick layout component based on route meta
const layout = computed(() => {
  const map: Record<string, object> = {
    auth: AuthLayout,
    student: StudentLayout,
    enterprise: EnterpriseLayout,
    admin: AdminLayout,
  }
  return map[route.meta.layout as string] ?? DefaultLayout
})

// Element Plus locale driven by languageStore
const epLocale = computed(() => {
  const map: Record<string, object> = { en: epEn, zh_cn: epZhCn, th: epTh }
  return map[languageStore.locale] ?? epEn
})

// Sync i18n locale with store on every locale change
languageStore.$subscribe(() => {
  locale.value = languageStore.locale
})

// App bootstrap
onMounted(async () => {
  // Sync i18n from persisted locale
  locale.value = languageStore.locale

  // Parallel init: settings + auth
  await Promise.allSettled([
    settingsStore.fetchPublicConfig(),
    authStore.isLoggedIn ? authStore.fetchProfile() : Promise.resolve(),
  ])
})
</script>

<template>
  <el-config-provider :locale="epLocale">
    <component :is="layout">
      <router-view />
    </component>
  </el-config-provider>
</template>

