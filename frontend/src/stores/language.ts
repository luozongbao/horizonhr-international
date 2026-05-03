/**
 * Language / locale store.
 * Manages the active UI language and syncs with Vue i18n + Element Plus.
 * App.vue subscribes to this store to update ElConfigProvider locale.
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api'

export type SupportedLocale = 'en' | 'zh_cn' | 'th'

export const SUPPORTED_LOCALES: { code: SupportedLocale; label: string; nativeLabel: string }[] = [
  { code: 'en',    label: 'English',  nativeLabel: 'English'  },
  { code: 'zh_cn', label: 'Chinese',  nativeLabel: '简体中文'   },
  { code: 'th',    label: 'Thai',     nativeLabel: 'ภาษาไทย'  },
]

const STORAGE_KEY = 'locale'

export const useLanguageStore = defineStore('language', () => {
  const savedLocale = (localStorage.getItem(STORAGE_KEY) as SupportedLocale) ?? 'en'
  const locale     = ref<SupportedLocale>(savedLocale)
  const available  = ref<typeof SUPPORTED_LOCALES>(SUPPORTED_LOCALES)

  const currentLocale = computed(() => locale.value)
  const currentLocaleLabel = computed(
    () => SUPPORTED_LOCALES.find((l) => l.code === locale.value)?.nativeLabel ?? 'English',
  )

  /**
   * Switch active locale.
   * App.vue's `$subscribe` listener picks up this change and updates
   * Vue i18n locale + ElConfigProvider locale automatically.
   */
  function setLocale(newLocale: SupportedLocale) {
    locale.value = newLocale
    localStorage.setItem(STORAGE_KEY, newLocale)
  }

  /** Alias for setLocale — used in LanguageSwitcher component */
  function switchLanguage(lang: SupportedLocale) {
    setLocale(lang)
  }

  /** Fetch active languages from backend (optional — falls back to defaults) */
  async function fetchAvailableLanguages() {
    try {
      const { data } = await api.get('/public/languages')
      if (data?.data?.length) {
        available.value = (data.data as Array<{ code: string; name: string; native_name: string }>).map((l) => ({
          code: l.code as SupportedLocale,
          label: l.name,
          nativeLabel: l.native_name,
        }))
      }
    } catch {
      // non-fatal — fall back to hardcoded list
    }
  }

  return {
    locale,
    available,
    currentLocale,
    currentLocaleLabel,
    setLocale,
    switchLanguage,
    fetchAvailableLanguages,
    SUPPORTED_LOCALES,
  }
})

