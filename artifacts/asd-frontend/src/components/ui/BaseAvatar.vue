<script setup lang="ts">
import { computed } from 'vue'
import type { Size } from '@/types'

interface Props {
  src?: string
  alt?: string
  name?: string
  size?: Size
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
})

/** Extract initials from a full name */
const initials = computed(() => {
  if (!props.name) return '?'
  const parts = props.name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
})

/** Stable color derived from the name */
const bgColor = computed(() => {
  const palette = [
    '#006233', '#D21034', '#C9A227', '#0369a1',
    '#7c3aed', '#0891b2', '#16a34a', '#dc2626',
  ]
  if (!props.name) return palette[0]
  let hash = 0
  for (let i = 0; i < props.name.length; i++) {
    hash = props.name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return palette[Math.abs(hash) % palette.length]
})

const hasImage = computed(() => Boolean(props.src))
</script>

<template>
  <span :class="['avatar', `avatar--${size}`]" :aria-label="alt ?? name">
    <img v-if="hasImage" :src="src" :alt="alt ?? name" class="avatar__img" loading="lazy" />
    <span v-else class="avatar__initials" :style="{ backgroundColor: bgColor }" aria-hidden="true">
      {{ initials }}
    </span>
  </span>
</template>

<style scoped>
.avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background-color: var(--color-neutral-200, #e2e8f0);
}

.avatar--xs { width: 1.5rem;  height: 1.5rem;  font-size: 0.625rem; }
.avatar--sm { width: 2rem;    height: 2rem;    font-size: 0.75rem; }
.avatar--md { width: 2.5rem;  height: 2.5rem;  font-size: 0.875rem; }
.avatar--lg { width: 3rem;    height: 3rem;    font-size: 1rem; }
.avatar--xl { width: 4rem;    height: 4rem;    font-size: 1.375rem; }

.avatar__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar__initials {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 600;
  letter-spacing: 0.025em;
}
</style>
