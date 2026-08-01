import { createI18n } from 'vue-i18n'
import type { Locale } from '@/types'
import { DEFAULT_LOCALE, FALLBACK_LOCALE, SUPPORTED_LOCALES } from '@/types'
import ar from './locales/ar'
import fr from './locales/fr'
import en from './locales/en'

export type MessageSchema = typeof fr

function getInitialLocale(): Locale {
  const savedLocale = localStorage.getItem('asd:locale') as Locale | null
  if (savedLocale && SUPPORTED_LOCALES.includes(savedLocale)) return savedLocale
  const browserLocale = navigator.language.split('-')[0] as Locale
  if (SUPPORTED_LOCALES.includes(browserLocale)) return browserLocale
  return DEFAULT_LOCALE
}

export const i18n = createI18n({
  legacy: false,
  locale: getInitialLocale(),
  fallbackLocale: FALLBACK_LOCALE,
  messages: { ar, fr, en },
})

export default i18n
