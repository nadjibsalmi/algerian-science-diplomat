import { computed } from 'vue'
import { useI18n as useVueI18n } from 'vue-i18n'
import type { Locale } from '@/types'
import { LOCALE_LABELS, LOCALE_DIRECTIONS, SUPPORTED_LOCALES } from '@/types'

/**
 * Extends vue-i18n's useI18n with locale management helpers
 * and persists the chosen locale to localStorage.
 */
export function useLocale() {
  const { locale, t, te, d, n } = useVueI18n()

  const currentLocale = computed(() => locale.value as Locale)

  const availableLocales = computed(() =>
    SUPPORTED_LOCALES.map((l) => ({
      value: l,
      label: LOCALE_LABELS[l],
      direction: LOCALE_DIRECTIONS[l],
    }))
  )

  function setLocale(newLocale: Locale) {
    locale.value = newLocale
    localStorage.setItem('asd:locale', newLocale)
  }

  return { locale: currentLocale, setLocale, availableLocales, t, te, d, n }
}
