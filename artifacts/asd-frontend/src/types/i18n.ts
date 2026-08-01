export type Locale = 'ar' | 'fr' | 'en'

export type LocaleDirection = 'ltr' | 'rtl'

export const LOCALE_DIRECTIONS: Record<Locale, LocaleDirection> = {
  ar: 'rtl',
  fr: 'ltr',
  en: 'ltr',
}

export const LOCALE_LABELS: Record<Locale, string> = {
  ar: 'العربية',
  fr: 'Français',
  en: 'English',
}

export const DEFAULT_LOCALE: Locale = 'fr'
export const FALLBACK_LOCALE: Locale = 'en'
export const SUPPORTED_LOCALES: Locale[] = ['ar', 'fr', 'en']
