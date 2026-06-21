<script setup lang="ts">
defineProps<{
  label: string
  variant?: 'primary' | 'secondary' | 'danger'
  loading?: boolean
  disabled?: boolean
}>()

defineEmits<{ click: [] }>()
</script>

<template>
  <button
    :disabled="disabled || loading"
    class="px-4 py-2 rounded text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
    :class="{
      'bg-green-600 hover:bg-green-700 focus:ring-green-500 text-white':
        !variant || variant === 'primary',
      'bg-gray-700 hover:bg-gray-600 focus:ring-gray-500 text-white': variant === 'secondary',
      'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white': variant === 'danger',
    }"
    @click="$emit('click')"
  >
    <span v-if="loading" class="animate-pulse">···</span>
    <span v-else>{{ label }}</span>
  </button>
</template>
