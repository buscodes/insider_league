<script setup lang="ts">
import { Value } from '@/core/constants/Value'

defineProps<{
  modelValue: number | null
  error?: string | null
}>()

defineEmits<{
  'update:modelValue': [value: number]
}>()
</script>

<template>
  <div class="flex flex-col items-center gap-0.5">
    <input
      type="number"
      min="0"
      :value="modelValue ?? Value.ZERO"
      class="w-14 text-center rounded border bg-gray-800 text-white text-sm py-1 px-2 focus:outline-none focus:ring-1"
      :class="
        error
          ? 'border-red-500 focus:ring-red-500'
          : 'border-gray-600 focus:ring-green-500'
      "
      @input="$emit('update:modelValue', +($event.target as HTMLInputElement).value)"
    />
    <p v-if="error" class="text-red-400 text-xs whitespace-nowrap">{{ error }}</p>
  </div>
</template>
