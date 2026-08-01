import { ref, computed, onMounted, onUnmounted } from 'vue'
import { breakpoints } from '@/design-system'

type Breakpoint = keyof typeof breakpoints

function parsePx(value: string): number {
  return parseInt(value.replace('px', ''), 10)
}

/**
 * Reactive viewport breakpoints.
 * Usage: const { isMobile, isDesktop } = useBreakpoints()
 */
export function useBreakpoints() {
  const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)

  function onResize() {
    windowWidth.value = window.innerWidth
  }

  onMounted(() => window.addEventListener('resize', onResize))
  onUnmounted(() => window.removeEventListener('resize', onResize))

  const isMobile = computed(() => windowWidth.value < parsePx(breakpoints.md))

  const isTablet = computed(
    () =>
      windowWidth.value >= parsePx(breakpoints.md) &&
      windowWidth.value < parsePx(breakpoints.lg)
  )

  const isDesktop = computed(() => windowWidth.value >= parsePx(breakpoints.lg))

  function isAbove(bp: Breakpoint) {
    return computed(() => windowWidth.value >= parsePx(breakpoints[bp]))
  }

  function isBelow(bp: Breakpoint) {
    return computed(() => windowWidth.value < parsePx(breakpoints[bp]))
  }

  return { windowWidth, isMobile, isTablet, isDesktop, isAbove, isBelow }
}
