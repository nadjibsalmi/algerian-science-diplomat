<script setup lang="ts">
import type { Size } from '@/types'

interface Props {
  size?: Size
  color?: string
  label?: string
}

withDefaults(defineProps<Props>(), {
  size: 'md',
  label: 'Loading',
})
</script>

<template>
  <span
    :class="['spinner', `spinner--${size}`]"
    role="status"
    :aria-label="label"
  >
    <span class="spinner__ring" :style="color ? { borderTopColor: color } : {}" />
    <span class="sr-only">{{ label }}</span>
  </span>
</template>

<style scoped>
.spinner {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.spinner__ring {
  display: block;
  border-radius: 50%;
  border: 2px solid var(--color-neutral-200, #e2e8f0);
  border-top-color: var(--color-primary-500, #006233);
  animation: spin 0.7s linear infinite;
}

.spinner--xs .spinner__ring { width: 0.875rem; height: 0.875rem; }
.spinner--sm .spinner__ring { width: 1.125rem; height: 1.125rem; }
.spinner--md .spinner__ring { width: 1.5rem;   height: 1.5rem; border-width: 2px; }
.spinner--lg .spinner__ring { width: 2rem;     height: 2rem;   border-width: 3px; }
.spinner--xl .spinner__ring { width: 2.5rem;   height: 2.5rem; border-width: 3px; }

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
