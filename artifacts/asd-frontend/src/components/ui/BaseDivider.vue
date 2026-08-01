<script setup lang="ts">
interface Props {
  label?: string
  vertical?: boolean
  dashed?: boolean
}

withDefaults(defineProps<Props>(), {
  vertical: false,
  dashed: false,
})
</script>

<template>
  <div
    :class="['divider', { 'divider--vertical': vertical, 'divider--dashed': dashed }]"
    role="separator"
    :aria-orientation="vertical ? 'vertical' : 'horizontal'"
  >
    <span v-if="label && !vertical" class="divider__label">{{ label }}</span>
  </div>
</template>

<style scoped>
.divider {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--color-neutral-400, #94a3b8);
  font-size: 0.8125rem;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background-color: var(--color-neutral-200, #e2e8f0);
}

.divider--dashed::before,
.divider--dashed::after {
  background: repeating-linear-gradient(
    to right,
    var(--color-neutral-300, #cbd5e1) 0,
    var(--color-neutral-300, #cbd5e1) 4px,
    transparent 4px,
    transparent 8px
  );
}

.divider--vertical {
  flex-direction: column;
  align-self: stretch;
  width: 1px;
}

.divider--vertical::before,
.divider--vertical::after {
  width: 1px;
  height: auto;
  flex: 1;
}

.divider__label { white-space: nowrap; flex-shrink: 0; }
</style>
