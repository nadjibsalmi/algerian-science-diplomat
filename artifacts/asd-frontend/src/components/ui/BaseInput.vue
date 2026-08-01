<script setup lang="ts">
import { computed } from 'vue'
import type { Size } from '@/types'

interface Props {
  modelValue?: string | number
  type?: string
  placeholder?: string
  disabled?: boolean
  readonly?: boolean
  size?: Size
  error?: string
  label?: string
  hint?: string
  id?: string
  required?: boolean
  autocomplete?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  size: 'md',
  disabled: false,
  readonly: false,
  required: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  change: [value: string]
  blur: [event: FocusEvent]
  focus: [event: FocusEvent]
}>()

const inputId = computed(
  () => props.id ?? `input-${Math.random().toString(36).slice(2, 9)}`
)

const hasError = computed(() => Boolean(props.error))
</script>

<template>
  <div class="field">
    <label v-if="label" :for="inputId" class="field__label">
      {{ label }}
      <span v-if="required" class="field__required" aria-label="required">*</span>
    </label>

    <input
      :id="inputId"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :required="required"
      :autocomplete="autocomplete"
      :aria-invalid="hasError"
      :aria-describedby="hasError ? `${inputId}-error` : hint ? `${inputId}-hint` : undefined"
      :class="['field__input', `field__input--${size}`, { 'field__input--error': hasError }]"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      @change="emit('change', ($event.target as HTMLInputElement).value)"
      @blur="emit('blur', $event)"
      @focus="emit('focus', $event)"
    />

    <p
      v-if="hasError"
      :id="`${inputId}-error`"
      class="field__message field__message--error"
      role="alert"
    >
      {{ error }}
    </p>
    <p v-else-if="hint" :id="`${inputId}-hint`" class="field__message field__message--hint">
      {{ hint }}
    </p>
  </div>
</template>

<style scoped>
.field { display: flex; flex-direction: column; gap: 0.375rem; }

.field__label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-neutral-700, #334155);
}

.field__required { color: var(--color-secondary-500, #d21034); margin-inline-start: 0.125rem; }

.field__input {
  width: 100%;
  border: 1px solid var(--color-neutral-300, #cbd5e1);
  border-radius: var(--radius-md, 0.5rem);
  background-color: var(--color-white, #fff);
  color: var(--color-neutral-900, #0f172a);
  transition: border-color 150ms ease, box-shadow 150ms ease;
  outline: none;
  font-family: inherit;
}

.field__input::placeholder { color: var(--color-neutral-400, #94a3b8); }

.field__input:focus {
  border-color: var(--color-primary-500, #006233);
  box-shadow: 0 0 0 3px rgb(0 98 51 / 0.15);
}

.field__input:disabled {
  background-color: var(--color-neutral-100, #f1f5f9);
  cursor: not-allowed;
  opacity: 0.7;
}

.field__input--error {
  border-color: var(--color-secondary-500, #d21034);
}
.field__input--error:focus {
  border-color: var(--color-secondary-500, #d21034);
  box-shadow: 0 0 0 3px rgb(210 16 52 / 0.15);
}

.field__input--xs { height: 1.75rem; padding: 0 0.5rem;   font-size: 0.75rem; }
.field__input--sm { height: 2rem;    padding: 0 0.625rem; font-size: 0.875rem; }
.field__input--md { height: 2.5rem;  padding: 0 0.75rem;  font-size: 1rem; }
.field__input--lg { height: 2.75rem; padding: 0 1rem;     font-size: 1.125rem; }
.field__input--xl { height: 3rem;    padding: 0 1.125rem; font-size: 1.25rem; }

.field__message { font-size: 0.8125rem; margin-top: 0.125rem; }
.field__message--error { color: var(--color-secondary-500, #d21034); }
.field__message--hint  { color: var(--color-neutral-500, #64748b); }
</style>
