<script setup lang="ts">
import type { Variant, Size } from '@/types'

interface Props {
  variant?: Variant
  size?: Size
  pill?: boolean
  dot?: boolean
}

withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'sm',
  pill: false,
  dot: false,
})
</script>

<template>
  <span
    :class="[
      'badge',
      `badge--${variant}`,
      `badge--${size}`,
      { 'badge--pill': pill },
    ]"
  >
    <span v-if="dot" class="badge__dot" aria-hidden="true" />
    <slot />
  </span>
</template>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  font-weight: 500;
  border-radius: var(--radius-DEFAULT, 0.375rem);
  white-space: nowrap;
  line-height: 1;
}

.badge--pill { border-radius: var(--radius-full, 9999px); }

.badge--xs { padding: 0.125rem 0.375rem; font-size: 0.625rem; }
.badge--sm { padding: 0.25rem 0.5rem;   font-size: 0.75rem; }
.badge--md { padding: 0.375rem 0.625rem; font-size: 0.875rem; }
.badge--lg { padding: 0.5rem 0.75rem;   font-size: 1rem; }
.badge--xl { padding: 0.625rem 0.875rem; font-size: 1.125rem; }

.badge--primary   { background-color: #e6f2eb; color: #006233; }
.badge--secondary { background-color: #fce8ec; color: #d21034; }
.badge--success   { background-color: #dcfce7; color: #166534; }
.badge--warning   { background-color: #fef9c3; color: #713f12; }
.badge--danger    { background-color: #fee2e2; color: #7f1d1d; }
.badge--ghost     { background-color: var(--color-neutral-100, #f1f5f9); color: var(--color-neutral-700, #334155); }
.badge--outline {
  background-color: transparent;
  border: 1px solid var(--color-neutral-300, #cbd5e1);
  color: var(--color-neutral-700, #334155);
}

.badge__dot {
  width: 0.5em;
  height: 0.5em;
  border-radius: 50%;
  background-color: currentColor;
  flex-shrink: 0;
}
</style>
