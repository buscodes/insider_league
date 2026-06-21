<script setup lang="ts">
import type { BadgeType } from '@/application/models/BadgeType'

defineProps<{
  type: BadgeType
}>()
</script>

<template>
  <span
    class="px-2 py-0.5 rounded-full text-xs font-medium"
    :class="{
      'bg-green-900/50 text-green-300': type === 'played',
      'bg-gray-700 text-gray-400': type === 'pending',
    }"
  >
    {{ type === 'played' ? 'Played' : 'Pending' }}
  </span>
</template>
