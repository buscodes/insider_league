<script setup lang="ts">
import type { StandingRow } from '@/application/models/StandingRow'

defineProps<{
  row: StandingRow
  rank: number
}>()
</script>

<template>
  <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
    <td class="py-3 px-4 text-gray-400 text-sm">{{ rank }}</td>
    <td class="py-3 px-4 text-sm font-medium text-white">{{ row.team_name }}</td>
    <td class="py-3 px-4 text-center text-sm text-gray-300">{{ row.played }}</td>
    <td class="py-3 px-4 text-center text-sm text-green-400">{{ row.won }}</td>
    <td class="py-3 px-4 text-center text-sm text-yellow-400">{{ row.drawn }}</td>
    <td class="py-3 px-4 text-center text-sm text-red-400">{{ row.lost }}</td>
    <td class="py-3 px-4 text-center text-sm text-gray-300">{{ row.goals_for }}</td>
    <td class="py-3 px-4 text-center text-sm text-gray-300">{{ row.goals_against }}</td>
    <td class="py-3 px-4 text-center text-sm text-gray-300">
      {{ row.goal_difference > 0 ? '+' : '' }}{{ row.goal_difference }}
    </td>
    <td class="py-3 px-4 text-center text-sm font-bold text-white">{{ row.points }}</td>
  </tr>
</template>
