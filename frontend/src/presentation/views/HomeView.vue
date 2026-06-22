<script setup lang="ts">
import { onMounted } from 'vue'
import { useLeagueStore } from '@/presentation/stores/leagueStore'
import DashboardTemplate from '@/presentation/templates/DashboardTemplate.vue'
import FixturePreview from '@/presentation/components/organisms/FixturePreview.vue'
import AppButton from '@/presentation/components/atoms/AppButton.vue'
import { LeagueConstants } from '@/core/constants/LeagueConstants'

const store = useLeagueStore()

onMounted(async () => {
  await store.initialize()
})
</script>

<template>
  <div class="min-h-screen bg-gray-950 text-white">

    <header
      v-if="store.isSimulationStarted"
      class="bg-gray-900 border-b border-gray-800 sticky top-0 z-10"
    >
      <div class="flex items-center justify-between max-w-7xl mx-auto px-6 py-4">
        <div>
          <h1 class="text-xl font-bold text-white tracking-tight">
            Insider Champions League
          </h1>
          <p class="text-xs text-gray-400 mt-0.5">
            Week {{ store.currentWeek }} of {{ LeagueConstants.TOTAL_WEEKS }} completed
          </p>
        </div>
        <div class="flex items-center gap-2">
          <AppButton
            v-if="!store.allPlayed"
            label="Play Next Week"
            :loading="store.isLoading"
            :disabled="store.isLoading"
            @click="store.playNextWeek()"
          />
          <AppButton
            v-if="!store.allPlayed"
            label="Play All"
            variant="secondary"
            :loading="store.isLoading"
            :disabled="store.isLoading"
            @click="store.playAll()"
          />
          <AppButton
            label="Reset League"
            variant="danger"
            :disabled="store.isLoading"
            @click="store.resetLeague()"
          />
        </div>
      </div>
    </header>

    <div
      v-if="store.isLoading && !store.fixtureGenerated"
      class="flex items-center justify-center min-h-screen"
    >
      <p class="text-gray-400 animate-pulse text-sm">Loading…</p>
    </div>

    <div
      v-else-if="!store.fixtureGenerated"
      class="max-w-2xl mx-auto px-6 py-16"
    >
      <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-white tracking-tight">
          Insider Champions League
        </h1>
        <p class="text-gray-400 text-sm mt-2">
          4-team round-robin simulation with Monte Carlo predictions
        </p>
      </div>

      <!-- Teams table -->
      <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden mb-8">
        <div class="bg-gray-800 px-5 py-3">
          <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wide">
            Tournament Teams
          </h2>
        </div>
        <ul>
          <li
            v-for="(team, index) in store.teams"
            :key="team.id"
            class="flex items-center justify-between px-5 py-3.5 border-b border-gray-800 last:border-0"
          >
            <div class="flex items-center gap-3">
              <span class="text-xs text-gray-500 w-4 text-center">{{ index + 1 }}</span>
              <span class="text-sm font-medium text-white">{{ team.name }}</span>
            </div>
            <span class="text-xs text-gray-500 opacity-20">
              Power: <span class="text-green-400 font-mono">{{ team.power }}</span>
            </span>
          </li>
        </ul>
      </div>

      <div class="flex justify-center">
        <AppButton
          label="Generate Fixtures"
          :loading="store.isLoading"
          :disabled="store.isLoading"
          @click="store.generateFixtures()"
        />
      </div>
    </div>

    <div
      v-else-if="!store.isSimulationStarted"
      class="max-w-5xl mx-auto px-6 py-10"
    >
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-white tracking-tight">
            Insider Champions League
          </h1>
          <p class="text-gray-400 text-sm mt-1">
            Fixture ready — {{ LeagueConstants.TOTAL_WEEKS }} weeks,
            {{ LeagueConstants.TOTAL_MATCHES }} matches
          </p>
        </div>
        <AppButton
          label="Start Simulation"
          :disabled="store.isLoading"
          @click="store.startSimulation()"
        />
      </div>

      <FixturePreview />
    </div>

    <DashboardTemplate v-else />

  </div>
</template>
