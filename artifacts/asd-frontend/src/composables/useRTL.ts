import { computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import type { LocaleDirection, Locale } from '@/types'
import { LOCALE_DIRECTIONS } from '@/types'

/**
 * Provides reactive RTL direction based on the current locale.
 * Automatically updates <html dir> and <html lang> on locale change.
 */
export function useRTL() {
  const { locale } = useI18n()

  const direction = computed<LocaleDirection>(
    () => LOCALE_DIRECTIONS[locale.value as Locale] ?? 'ltr'
  )

  const isRTL = computed(() => direction.value === 'rtl')

  watch(
    direction,
    (dir) => {
      document.documentElement.dir = dir
      document.documentElement.lang = locale.value
      document.body.setAttribute('dir', dir)
      // Apply RTL-specific font for Arabic
      document.documentElement.style.setProperty(
        '--font-family-base',
        dir === 'rtl'
          ? '"Noto Sans Arabic", "Inter", system-ui, sans-serif'
          : '"Inter", "Noto Sans Arabic", system-ui, sans-serif'
      )
    },
    { immediate: true }
  )

  return { direction, isRTL }
}
