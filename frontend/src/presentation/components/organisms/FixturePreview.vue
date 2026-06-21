<script setup lang="ts">
import { computed } from 'vue'
import { useLeagueStore } from '@/presentation/stores/leagueStore'
import { LeagueConstants } from '@/core/constants/LeagueConstants'
import { Value } from '@/core/constants/Value'

const store = useLeagueStore()

const weeks = computed<number[]>(() =>
  Array.from({ length: LeagueConstants.TOTAL_WEEKS }, (_, i) => i + Value.ONE),
)
</script>

<template>
  <section>
    <h2 class="text-lg font-semibold text-white mb-4">Generated Fixtures</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="week in weeks"
        :key="week"
        class="bg-gray-800 rounded-xl border border-gray-700 p-4"
      >
        <p class="text-xs font-semibold text-green-400 uppercase tracking-wider mb-3">
          Week {{ week }}
        </p>
        <div
          v-for="match in store.fixturesByWeek[week]"
          :key="match.id"
          class="flex items-center justify-between gap-2 py-2 border-b border-gray-700 last:border-0"
        >
          <span class="text-sm text-white font-medium flex-1 text-right truncate">
            {{ match.home_team.name }}
          </span>
          <span class="text-xs text-gray-500 shrink-0">vs</span>
          <span class="text-sm text-white font-medium flex-1 truncate">
            {{ match.away_team.name }}
          </span>
        </div>
      </div>
    </div>
  </section>
</template>
