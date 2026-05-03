/**
 * Language / locale store.
 * Manages the active UI language and syncs with Vue i18n + backend API header.
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

export type SupportedLocale = 'en' | 'zh_cn' | 'th'

export const SUPPORTED_LOCALES: { code: SupportedLocale; label: string; nativeLabel: string }[] = [
  { code: 'en',    label: 'English',  nativeLabel: 'English'  },
  { code: 'zh_cn', label: 'Chinese',  nativeLabel: '简体中文'   },
  { code: 'th',    label: 'Thai',     nativeLabel: 'ภาษาไทย'  },
]

const STORAGE_KEY = 'locale'

export const useLanguageStore = defineStore('language', () => {
  const savedLocale = (localStorage.getItem(STORAGE_KEY) as SupportedLocale) ?? 'en'
  const locale = ref<SupportedLocale>(savedLocale)

  const currentLocale = computed(() => locale.value)

  const currentLocaleLabel = computed(
    () => SUPPORTED_LOCALES.find((l) => l.code === locale.value)?.nativeLabel ?? 'English',
  )

  /**
   * Switch the active locale.
   * Also updates Vue i18n and localStorage so the choice persists across reloads.
   */
  function setLocale(newLocale: SupportedLocale) {
    locale.value = newLocale
    localStorage.setItem(STORAGE_KEY, newLocale)

    // Update Vue i18n — must be called inside a component context (use composable)
    // The LanguageSwitcher component calls this and then updates i18n directly.
  }

  return {
    locale,
    currentLocale,
    currentLocaleLabel,
    setLocale,
    SUPPORTED_LOCALES,
  }
})
