import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Team } from '@/application/models/Team'
import type { Match } from '@/application/models/Match'
import type { StandingRow } from '@/application/models/StandingRow'
import type { Prediction } from '@/application/models/Prediction'
import type { ApiError } from '@/core/models/ApiError'
import { LeagueConstants } from '@/core/constants/LeagueConstants'
import { Value } from '@/core/constants/Value'
import { getTeams } from '@/application/services/TeamService'
import { getFixtures, generateFixtures as apiGenerateFixtures } from '@/application/services/FixtureService'
import { getLeagueTable, getPredictions } from '@/application/services/LeagueService'
import {
  playNextWeek as apiPlayNextWeek,
  playAll as apiPlayAll,
  resetLeague as apiResetLeague,
  updateMatchScore as apiUpdateMatchScore,
} from '@/application/services/SimulationService'

export const useLeagueStore = defineStore('league', () => {
  const teams = ref<Team[]>([])
  const fixtures = ref<Match[]>([])
  const standings = ref<StandingRow[]>([])
  const predictions = ref<Prediction[]>([])
  const currentWeek = ref<number>(Value.ZERO)
  const isLoading = ref<boolean>(Value.FALSE)
  const fixtureGenerated = ref<boolean>(Value.FALSE)
  const isSimulationStarted = ref<boolean>(Value.FALSE)
  const scoreErrors = ref<Record<number, Record<string, string[]>>>({})

  // Groups all fixtures by week number for tab-based display
  const fixturesByWeek = computed<Record<number, Match[]>>(() => {
    const grouped: Record<number, Match[]> = {}
    for (let w = Value.ONE; w <= LeagueConstants.TOTAL_WEEKS; w++) {
      grouped[w] = fixtures.value.filter((m) => m.week === w)
    }
    return grouped
  })

  // True from week 4 onward — drives prediction panel visibility
  const hasPredictions = computed<boolean>(
    () => currentWeek.value >= LeagueConstants.MIN_PREDICTION_WEEK,
  )

  // True only when every fixture in the season has been played
  const allPlayed = computed<boolean>(
    () =>
      fixtures.value.length > Value.ZERO && fixtures.value.every((m) => m.is_played),
  )

  // Week of the first unplayed fixture; falls back to final week when all are played
  const activeWeek = computed<number>(() => {
    if (fixtures.value.length === Value.ZERO) return Value.ONE
    const unplayed = fixtures.value.find((m) => !m.is_played)
    return unplayed?.week ?? LeagueConstants.TOTAL_WEEKS
  })

  // Derives completed-week count from played fixture count
  function computeCurrentWeek(): void {
    const played = fixtures.value.filter((m) => m.is_played).length
    currentWeek.value = Math.floor(played / LeagueConstants.MATCHES_PER_WEEK)
  }

  // Patches fixture list in-place with the server's updated match objects
  function mergeFixtures(updated: Match[]): void {
    const map = new Map(updated.map((m) => [m.id, m]))
    fixtures.value = fixtures.value.map((m) => map.get(m.id) ?? m)
  }

  // Fetches and overwrites the current league table
  async function refreshStandings(): Promise<void> {
    standings.value = await getLeagueTable()
  }

  // Fetches championship predictions; skips write when API returns null (before week 4)
  async function refreshPredictions(): Promise<void> {
    const result = await getPredictions()
    if (result !== null) {
      predictions.value = result
    }
  }

  // Refreshes standings, week counter, and predictions after any simulation mutation
  async function refreshLeagueData(): Promise<void> {
    await refreshStandings()
    computeCurrentWeek()
    if (hasPredictions.value) {
      await refreshPredictions()
    }
  }

  // Loads teams and fixtures on mount; restores simulation phase if matches were already played
  async function initialize(): Promise<void> {
    isLoading.value = Value.TRUE
    try {
      const [fetchedTeams, fetchedFixtures] = await Promise.all([
        getTeams(),
        getFixtures(),
      ])
      teams.value = fetchedTeams
      fixtures.value = fetchedFixtures
      fixtureGenerated.value = fetchedFixtures.length > Value.ZERO

      if (fixtureGenerated.value) {
        const hasPlayedMatches = fetchedFixtures.some((m) => m.is_played)
        if (hasPlayedMatches) {
          isSimulationStarted.value = Value.TRUE
          await refreshLeagueData()
        }
      }
    } finally {
      isLoading.value = Value.FALSE
    }
  }

  // Calls the API to generate the round-robin schedule and advances to fixture-review phase
  async function generateFixtures(): Promise<void> {
    isLoading.value = Value.TRUE
    try {
      fixtures.value = await apiGenerateFixtures()
      fixtureGenerated.value = Value.TRUE
    } finally {
      isLoading.value = Value.FALSE
    }
  }

  // Advances the UI from fixture-review to live simulation phase
  function startSimulation(): void {
    isSimulationStarted.value = Value.TRUE
  }

  // Simulates the next unplayed week and refreshes league data
  async function playNextWeek(): Promise<void> {
    isLoading.value = Value.TRUE
    try {
      const updated = await apiPlayNextWeek()
      mergeFixtures(updated)
      await refreshLeagueData()
    } finally {
      isLoading.value = Value.FALSE
    }
  }

  // Simulates all remaining weeks at once and refreshes league data
  async function playAll(): Promise<void> {
    isLoading.value = Value.TRUE
    try {
      const updated = await apiPlayAll()
      mergeFixtures(updated)
      await refreshLeagueData()
    } finally {
      isLoading.value = Value.FALSE
    }
  }

  // Resets all state to the pre-league phase
  async function resetLeague(): Promise<void> {
    isLoading.value = Value.TRUE
    try {
      await apiResetLeague()
      fixtures.value = []
      standings.value = []
      predictions.value = []
      currentWeek.value = Value.ZERO
      fixtureGenerated.value = Value.FALSE
      isSimulationStarted.value = Value.FALSE
      scoreErrors.value = {}
    } finally {
      isLoading.value = Value.FALSE
    }
  }

  // Sends a score edit to the API, updates the fixture in state, and refreshes league data
  async function updateMatchScore(
    matchId: number,
    homeScore: number,
    awayScore: number,
  ): Promise<boolean> {
    try {
      const updated = await apiUpdateMatchScore(matchId, homeScore, awayScore)
      const idx = fixtures.value.findIndex((m) => m.id === matchId)
      if (idx !== -1) fixtures.value[idx] = updated
      clearScoreErrors(matchId)
      await refreshLeagueData()
      return Value.TRUE
    } catch (err) {
      const apiError = err as ApiError
      if (apiError.errors) {
        scoreErrors.value = { ...scoreErrors.value, [matchId]: apiError.errors }
      }
      return Value.FALSE
    }
  }

  // Removes validation errors for the given match
  function clearScoreErrors(matchId: number): void {
    const next = { ...scoreErrors.value }
    delete next[matchId]
    scoreErrors.value = next
  }

  return {
    teams,
    fixtures,
    standings,
    predictions,
    currentWeek,
    isLoading,
    fixtureGenerated,
    isSimulationStarted,
    scoreErrors,
    fixturesByWeek,
    hasPredictions,
    allPlayed,
    activeWeek,
    initialize,
    generateFixtures,
    startSimulation,
    playNextWeek,
    playAll,
    resetLeague,
    updateMatchScore,
    clearScoreErrors,
  }
})
