import { createI18n } from 'vue-i18n'
import en from './locales/en.json'
import zh_cn from './locales/zh_cn.json'
import th from './locales/th.json'

const savedLocale = localStorage.getItem('locale') ?? 'en'

const i18n = createI18n({
  legacy: false,           // Use Composition API mode
  locale: savedLocale,
  fallbackLocale: 'en',
  messages: {
    en,
    zh_cn,
    th,
  },
})

export default i18n
