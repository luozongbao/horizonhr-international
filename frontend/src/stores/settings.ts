import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api'

type Locale = 'en' | 'zh_cn' | 'th'

interface PublicConfig {
  site_name: string
  contact_email: string
  contact_phone: string
  wechat_qr_url: string
  maintenance_mode: boolean
}

export const useSettingsStore = defineStore('settings', () => {
  const locale = ref<Locale>((localStorage.getItem('locale') as Locale) ?? 'en')
  const config = ref<PublicConfig | null>(null)

  function setLocale(l: Locale) {
    locale.value = l
    localStorage.setItem('locale', l)
  }

  async function fetchPublicConfig() {
    try {
      const { data } = await api.get('/public/settings')
      config.value = data
    } catch {
      // non-fatal — app can operate without remote config
    }
  }

  return { locale, config, setLocale, fetchPublicConfig }
})
