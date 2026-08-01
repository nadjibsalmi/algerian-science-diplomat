<script setup lang="ts">
import { useRTL } from '@/composables'

// RTL is initialized here so it applies globally from the root component
useRTL()
</script>

<template>
  <RouterView />
</template>
