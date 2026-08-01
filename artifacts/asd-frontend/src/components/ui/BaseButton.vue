<script setup lang="ts">
import type { Variant, Size } from '@/types'

interface Props {
  variant?: Variant
  size?: Size
  disabled?: boolean
  loading?: boolean
  type?: 'button' | 'submit' | 'reset'
  fullWidth?: boolean
  as?: 'button' | 'a'
  href?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  disabled: false,
  loading: false,
  type: 'button',
  fullWidth: false,
  as: 'button',
})

const emit = defineEmits<{
  click: [event: MouseEvent]
}>()

function handleClick(event: MouseEvent) {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<template>
  <component
    :is="as"
    :type="as === 'button' ? type : undefined"
    :href="as === 'a' ? href : undefined"
    :disabled="as === 'button' ? disabled || loading : undefined"
    :aria-disabled="disabled || loading"
    :aria-busy="loading"
    :class="[
      'btn',
      `btn--${variant}`,
      `btn--${size}`,
      { 'btn--full': fullWidth, 'btn--loading': loading, 'btn--disabled': disabled },
    ]"
    @click="handleClick"
  >
    <span v-if="loading" class="btn__spinner" aria-hidden="true" />
    <slot />
  </component>
</template>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border-radius: var(--radius-md, 0.5rem);
  font-weight: 500;
  font-family: inherit;
  cursor: pointer;
  transition:
    background-color 150ms ease,
    border-color 150ms ease,
    opacity 150ms ease,
    transform 100ms ease;
  border: 1px solid transparent;
  outline: none;
  position: relative;
  white-space: nowrap;
  user-select: none;
  text-decoration: none;
  line-height: 1;
}

.btn:focus-visible {
  outline: 2px solid var(--color-primary-500, #006233);
  outline-offset: 2px;
}

.btn--disabled,
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

.btn--full { width: 100%; }

/* ── Sizes ─────────────────────────────────────────────── */
.btn--xs { height: 1.75rem; padding: 0 0.625rem; font-size: 0.75rem; }
.btn--sm { height: 2rem;    padding: 0 0.75rem;  font-size: 0.875rem; }
.btn--md { height: 2.5rem;  padding: 0 1rem;     font-size: 1rem; }
.btn--lg { height: 2.75rem; padding: 0 1.25rem;  font-size: 1.125rem; }
.btn--xl { height: 3rem;    padding: 0 1.5rem;   font-size: 1.25rem; }

/* ── Variants ───────────────────────────────────────────── */
.btn--primary {
  background-color: var(--color-primary-500, #006233);
  color: #fff;
}
.btn--primary:hover:not(.btn--disabled):not(:disabled) {
  background-color: var(--color-primary-600, #005229);
}

.btn--secondary {
  background-color: var(--color-secondary-500, #d21034);
  color: #fff;
}
.btn--secondary:hover:not(.btn--disabled):not(:disabled) {
  background-color: var(--color-secondary-600, #b00e2c);
}

.btn--success  { background-color: #16a34a; color: #fff; }
.btn--success:hover:not(.btn--disabled):not(:disabled)  { background-color: #15803d; }

.btn--warning  { background-color: #ca8a04; color: #fff; }
.btn--warning:hover:not(.btn--disabled):not(:disabled)  { background-color: #a16207; }

.btn--danger   { background-color: #dc2626; color: #fff; }
.btn--danger:hover:not(.btn--disabled):not(:disabled)   { background-color: #b91c1c; }

.btn--ghost {
  background-color: transparent;
  color: var(--color-neutral-700, #334155);
}
.btn--ghost:hover:not(.btn--disabled):not(:disabled) {
  background-color: var(--color-neutral-100, #f1f5f9);
}

.btn--outline {
  background-color: transparent;
  border-color: var(--color-neutral-300, #cbd5e1);
  color: var(--color-neutral-700, #334155);
}
.btn--outline:hover:not(.btn--disabled):not(:disabled) {
  background-color: var(--color-neutral-50, #f8fafc);
}

/* ── Loading spinner ────────────────────────────────────── */
.btn__spinner {
  flex-shrink: 0;
  width: 1em;
  height: 1em;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: btn-spin 0.65s linear infinite;
}

@keyframes btn-spin {
  to { transform: rotate(360deg); }
}
</style>
