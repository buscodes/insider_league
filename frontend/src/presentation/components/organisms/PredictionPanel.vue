<script setup lang="ts">
import { computed } from 'vue'
import { useLeagueStore } from '@/presentation/stores/leagueStore'
import type { Prediction } from '@/application/models/Prediction'
import { Value } from '@/core/constants/Value'

const store = useLeagueStore()

const displayedPredictions = computed<Prediction[]>(() => {
  if (store.predictions.length > Value.ZERO) {
    return store.predictions
  }
  return store.teams.map((t) => ({
    team_name: t.name,
    championship_prediction: Value.ZERO,
  }))
})
</script>

<template>
  <section>
    <h2 class="text-lg font-semibold text-white mb-3">Championship Predictions</h2>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 space-y-4">
      <div
        v-for="pred in displayedPredictions"
        :key="pred.team_name"
        class="space-y-1.5"
      >
        <div class="flex items-center justify-between text-sm">
          <span class="text-white font-medium">{{ pred.team_name }}</span>
          <span class="text-green-400 font-mono tabular-nums">
            {{ pred.championship_prediction.toFixed(1) }}%
          </span>
        </div>
        <div class="h-2 rounded-full bg-gray-700 overflow-hidden">
          <div
            class="h-full rounded-full bg-green-500 transition-all duration-700 ease-out"
            :style="{ width: `${pred.championship_prediction}%` }"
          />
        </div>
      </div>
    </div>
  </section>
</template>
