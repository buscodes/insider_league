<script setup lang="ts">
import LeagueTable from '@/presentation/components/organisms/LeagueTable.vue'
import WeeklyFixtures from '@/presentation/components/organisms/WeeklyFixtures.vue'
import PredictionPanel from '@/presentation/components/organisms/PredictionPanel.vue'
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6 max-w-7xl mx-auto">
    <div class="space-y-6">
      <LeagueTable />
      <PredictionPanel />
    </div>
    <WeeklyFixtures />
  </div>
</template>
