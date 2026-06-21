<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import AppButton from '@/presentation/components/atoms/AppButton.vue'
import AppBadge from '@/presentation/components/atoms/AppBadge.vue'
import ScoreInput from '@/presentation/components/atoms/ScoreInput.vue'
import { useLeagueStore } from '@/presentation/stores/leagueStore'
import type { Match } from '@/application/models/Match'
import type { BadgeType } from '@/application/models/BadgeType'
import { Value } from '@/core/constants/Value'

const props = defineProps<{ match: Match }>()

const store = useLeagueStore()
const isEditing = ref<boolean>(Value.FALSE)
const editHomeScore = ref<number>(Value.ZERO)
const editAwayScore = ref<number>(Value.ZERO)

const badgeType = computed<BadgeType>(() => (props.match.is_played ? 'played' : 'pending'))

const homeError = computed<string | null>(
  () => store.scoreErrors[props.match.id]?.home_score?.[0] ?? null,
)
const awayError = computed<string | null>(
  () => store.scoreErrors[props.match.id]?.away_score?.[0] ?? null,
)

watch(editHomeScore, () => store.clearScoreErrors(props.match.id))
watch(editAwayScore, () => store.clearScoreErrors(props.match.id))

function startEdit(): void {
  editHomeScore.value = props.match.home_score ?? Value.ZERO
  editAwayScore.value = props.match.away_score ?? Value.ZERO
  isEditing.value = Value.TRUE
}

function cancelEdit(): void {
  isEditing.value = Value.FALSE
  store.clearScoreErrors(props.match.id)
}

async function saveScore(): Promise<void> {
  const success = await store.updateMatchScore(
    props.match.id,
    editHomeScore.value,
    editAwayScore.value,
  )
  if (success) {
    isEditing.value = Value.FALSE
  }
}
</script>

<template>
  <div class="flex items-center justify-between gap-3 py-3 px-4 bg-gray-800 rounded-lg">
    <!-- Home team -->
    <span class="flex-1 text-right text-sm font-medium text-white truncate">
      {{ match.home_team.name }}
    </span>

    <!-- Score / Inputs -->
    <div class="flex items-start justify-center gap-2 min-w-[100px]">
      <template v-if="isEditing">
        <ScoreInput v-model="editHomeScore" :error="homeError" />
        <span class="text-gray-400 self-center text-sm">–</span>
        <ScoreInput v-model="editAwayScore" :error="awayError" />
      </template>
      <template v-else>
        <span class="text-sm font-mono text-white w-6 text-center">
          {{ match.is_played ? match.home_score : '–' }}
        </span>
        <span class="text-gray-500 text-sm">:</span>
        <span class="text-sm font-mono text-white w-6 text-center">
          {{ match.is_played ? match.away_score : '–' }}
        </span>
      </template>
    </div>

    <!-- Away team -->
    <span class="flex-1 text-sm font-medium text-white truncate">
      {{ match.away_team.name }}
    </span>

    <!-- Actions -->
    <div class="flex items-center gap-2 shrink-0">
      <AppBadge :type="badgeType" />

      <template v-if="isEditing">
        <AppButton label="Save" :loading="store.isLoading" @click="saveScore" />
        <AppButton label="Cancel" variant="secondary" @click="cancelEdit" />
      </template>
      <template v-else-if="match.is_played">
        <AppButton label="Edit" variant="secondary" @click="startEdit" />
      </template>
    </div>
  </div>
</template>
