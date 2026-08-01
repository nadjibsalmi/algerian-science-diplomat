<script setup lang="ts">
interface Props {
  /** Display a subtle shadow */
  shadow?: boolean
  /** Remove inner padding */
  noPadding?: boolean
  /** Make the card interactive (hover highlight) */
  interactive?: boolean
  /** Remove the border */
  borderless?: boolean
  as?: string
}

withDefaults(defineProps<Props>(), {
  shadow: false,
  noPadding: false,
  interactive: false,
  borderless: false,
  as: 'div',
})
</script>

<template>
  <component
    :is="as"
    :class="[
      'card',
      { 'card--shadow': shadow, 'card--no-padding': noPadding, 'card--interactive': interactive, 'card--borderless': borderless },
    ]"
  >
    <div v-if="$slots.header" class="card__header">
      <slot name="header" />
    </div>

    <div class="card__body">
      <slot />
    </div>

    <div v-if="$slots.footer" class="card__footer">
      <slot name="footer" />
    </div>
  </component>
</template>

<style scoped>
.card {
  background-color: var(--color-white, #fff);
  border: 1px solid var(--color-neutral-200, #e2e8f0);
  border-radius: var(--radius-lg, 0.75rem);
  overflow: hidden;
  transition: box-shadow 200ms ease, transform 150ms ease;
}

.card--shadow    { box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1); }
.card--borderless { border-color: transparent; }
.card--interactive { cursor: pointer; }
.card--interactive:hover {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  transform: translateY(-1px);
}

.card__header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--color-neutral-100, #f1f5f9);
  font-weight: 600;
  color: var(--color-neutral-900, #0f172a);
}

.card__body { padding: 1.5rem; }
.card--no-padding .card__body { padding: 0; }

.card__footer {
  padding: 0.75rem 1.5rem;
  border-top: 1px solid var(--color-neutral-100, #f1f5f9);
  background-color: var(--color-neutral-50, #f8fafc);
}
</style>
