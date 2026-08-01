<script setup lang="ts">
import { watch, onUnmounted } from 'vue'
import type { Size } from '@/types'

interface Props {
  open: boolean
  title?: string
  size?: Extract<Size, 'sm' | 'md' | 'lg' | 'xl'>
  closable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  closable: true,
})

const emit = defineEmits<{ close: [] }>()

function closeModal() {
  if (props.closable) emit('close')
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') closeModal()
}

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      document.addEventListener('keydown', handleKeydown)
      document.body.style.overflow = 'hidden'
    } else {
      document.removeEventListener('keydown', handleKeydown)
      document.body.style.overflow = ''
    }
  }
)

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="open" class="modal-overlay" @click.self="closeModal">
        <div
          :class="['modal', `modal--${size}`]"
          role="dialog"
          :aria-modal="true"
          :aria-labelledby="title ? 'modal-title' : undefined"
        >
          <div v-if="title || closable" class="modal__header">
            <h2 v-if="title" id="modal-title" class="modal__title">{{ title }}</h2>
            <button
              v-if="closable"
              class="modal__close"
              type="button"
              aria-label="Close"
              @click="closeModal"
            >
              ×
            </button>
          </div>

          <div class="modal__body">
            <slot />
          </div>

          <div v-if="$slots.footer" class="modal__footer">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgb(0 0 0 / 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: var(--z-modal, 1300);
  padding: 1rem;
}

.modal {
  background-color: var(--color-white, #fff);
  border-radius: var(--radius-xl, 1rem);
  box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  width: 100%;
  overflow: hidden;
}

.modal--sm { max-width: 24rem; }
.modal--md { max-width: 32rem; }
.modal--lg { max-width: 48rem; }
.modal--xl { max-width: 64rem; }

.modal__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--color-neutral-100, #f1f5f9);
  flex-shrink: 0;
}

.modal__title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--color-neutral-900, #0f172a);
  margin: 0;
}

.modal__close {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.5rem;
  line-height: 1;
  color: var(--color-neutral-500, #64748b);
  padding: 0.25rem;
  border-radius: var(--radius-DEFAULT, 0.375rem);
  transition: color 150ms ease, background-color 150ms ease;
}
.modal__close:hover { color: var(--color-neutral-900, #0f172a); background-color: var(--color-neutral-100, #f1f5f9); }

.modal__body { padding: 1.5rem; overflow-y: auto; flex: 1; }

.modal__footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-neutral-100, #f1f5f9);
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  flex-shrink: 0;
}

/* Transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 200ms ease;
}
.modal-enter-active .modal,
.modal-leave-active .modal {
  transition: transform 200ms ease, opacity 200ms ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from .modal {
  transform: scale(0.95) translateY(-0.5rem);
}
.modal-leave-to .modal {
  transform: scale(0.95) translateY(-0.5rem);
  opacity: 0;
}
</style>
