<script setup lang="ts">
import { useLeagueStore } from '@/presentation/stores/leagueStore'
import StandingRow from '@/presentation/components/molecules/StandingRow.vue'

const store = useLeagueStore()
</script>

<template>
  <section>
    <h2 class="text-lg font-semibold text-white mb-3">League Table</h2>
    <div class="bg-gray-900 rounded-xl overflow-hidden border border-gray-800">
      <table class="w-full">
        <thead>
          <tr class="bg-gray-800 text-xs text-gray-400 uppercase tracking-wide">
            <th class="py-2 px-4 text-left">#</th>
            <th class="py-2 px-4 text-left">Team</th>
            <th class="py-2 px-4 text-center">P</th>
            <th class="py-2 px-4 text-center">W</th>
            <th class="py-2 px-4 text-center">D</th>
            <th class="py-2 px-4 text-center">L</th>
            <th class="py-2 px-4 text-center">GF</th>
            <th class="py-2 px-4 text-center">GA</th>
            <th class="py-2 px-4 text-center">GD</th>
            <th class="py-2 px-4 text-center">Pts</th>
          </tr>
        </thead>
        <tbody>
          <StandingRow
            v-for="(row, index) in store.standings"
            :key="row.team_name"
            :row="row"
            :rank="index + 1"
          />
        </tbody>
      </table>
      <p v-if="store.standings.length === 0" class="text-center text-gray-500 text-sm py-6">
        No matches played yet.
      </p>
    </div>
  </section>
</template>
