<script setup lang="ts">
import type { Variant } from '@/types'

interface Props {
  variant?: Extract<Variant, 'primary' | 'success' | 'warning' | 'danger'>
  title?: string
  dismissible?: boolean
}

withDefaults(defineProps<Props>(), {
  variant: 'primary',
  dismissible: false,
})

const emit = defineEmits<{ dismiss: [] }>()
</script>

<template>
  <div :class="['alert', `alert--${variant}`]" role="alert">
    <div class="alert__icon" aria-hidden="true">
      <slot name="icon">
        <span v-if="variant === 'success'">✓</span>
        <span v-else-if="variant === 'warning'">⚠</span>
        <span v-else-if="variant === 'danger'">✕</span>
        <span v-else>ℹ</span>
      </slot>
    </div>
    <div class="alert__content">
      <p v-if="title" class="alert__title">{{ title }}</p>
      <div class="alert__body"><slot /></div>
    </div>
    <button
      v-if="dismissible"
      class="alert__dismiss"
      type="button"
      :aria-label="'Dismiss'"
      @click="emit('dismiss')"
    >
      ×
    </button>
  </div>
</template>

<style scoped>
.alert {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border-radius: var(--radius-md, 0.5rem);
  border-inline-start: 4px solid transparent;
  font-size: 0.9375rem;
}

.alert--primary  { background: #e6f2eb; border-color: #006233; color: #004d28; }
.alert--success  { background: #dcfce7; border-color: #16a34a; color: #166534; }
.alert--warning  { background: #fef9c3; border-color: #ca8a04; color: #713f12; }
.alert--danger   { background: #fee2e2; border-color: #dc2626; color: #7f1d1d; }

.alert__icon { font-size: 1rem; font-weight: 700; flex-shrink: 0; line-height: 1.5; }

.alert__content { flex: 1; min-width: 0; }

.alert__title {
  font-weight: 600;
  margin-bottom: 0.25rem;
  font-size: 0.9375rem;
}

.alert__body { font-size: 0.875rem; line-height: 1.5; }

.alert__dismiss {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.25rem;
  line-height: 1;
  color: inherit;
  opacity: 0.6;
  padding: 0;
  flex-shrink: 0;
  transition: opacity 150ms ease;
}
.alert__dismiss:hover { opacity: 1; }
</style>
