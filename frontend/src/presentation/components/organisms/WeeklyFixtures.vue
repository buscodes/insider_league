<script setup lang="ts">
import { ref, computed } from 'vue'
import { useLeagueStore } from '@/presentation/stores/leagueStore'
import MatchRow from '@/presentation/components/molecules/MatchRow.vue'
import { LeagueConstants } from '@/core/constants/LeagueConstants'
import { Value } from '@/core/constants/Value'

const store = useLeagueStore()
const selectedWeek = ref<number>(Value.ONE)

const weeks = computed<number[]>(() =>
  Array.from({ length: LeagueConstants.TOTAL_WEEKS }, (_, i) => i + Value.ONE),
)

const displayedMatches = computed(() => store.fixturesByWeek[selectedWeek.value] ?? [])
</script>

<template>
  <section>
    <h2 class="text-lg font-semibold text-white mb-3">Weekly Fixtures</h2>

    <!-- Week tabs -->
    <div class="flex gap-1 mb-4 flex-wrap">
      <button
        v-for="week in weeks"
        :key="week"
        class="px-3 py-1.5 rounded text-xs font-medium transition-colors"
        :class="[
          selectedWeek === week
            ? 'bg-green-600 text-white'
            : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white',
          store.activeWeek === week && selectedWeek !== week
            ? 'ring-1 ring-green-500'
            : '',
        ]"
        @click="selectedWeek = week"
      >
        Week {{ week }}
      </button>
    </div>

    <!-- Matches -->
    <div class="space-y-2">
      <MatchRow
        v-for="match in displayedMatches"
        :key="match.id"
        :match="match"
      />
      <p v-if="displayedMatches.length === 0" class="text-center text-gray-500 text-sm py-8">
        No matches for this week.
      </p>
    </div>
  </section>
</template>
