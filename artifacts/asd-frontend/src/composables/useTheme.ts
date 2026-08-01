import { ref, computed, watch } from 'vue'
import type { ColorScheme } from '@/types'

const colorScheme = ref<ColorScheme>(
  (localStorage.getItem('asd:theme') as ColorScheme | null) ?? 'system'
)

function resolveIsDark(scheme: ColorScheme): boolean {
  if (scheme === 'dark') return true
  if (scheme === 'light') return false
  return window.matchMedia('(prefers-color-scheme: dark)').matches
}

function applyTheme(scheme: ColorScheme) {
  const isDark = resolveIsDark(scheme)
  document.documentElement.classList.toggle('dark', isDark)
  document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light')
}

// Listen for OS-level theme changes when in 'system' mode
if (typeof window !== 'undefined') {
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (colorScheme.value === 'system') applyTheme('system')
  })
}

/**
 * Provides reactive color scheme management with localStorage persistence.
 * Supported values: 'light' | 'dark' | 'system'
 */
export function useTheme() {
  const isDark = computed(() => resolveIsDark(colorScheme.value))

  function setColorScheme(scheme: ColorScheme) {
    colorScheme.value = scheme
    localStorage.setItem('asd:theme', scheme)
    applyTheme(scheme)
  }

  watch(colorScheme, applyTheme, { immediate: true })

  return { colorScheme, isDark, setColorScheme }
}
