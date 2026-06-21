export const ApiPaths = {
  teams: '/teams',
  fixtures: '/fixtures',
  fixturesGenerate: '/fixtures/generate',
  leagueTable: '/league-table',
  predictions: '/predictions',
  simulationPlayWeek: '/simulation/play-week',
  simulationPlayAll: '/simulation/play-all',
  simulationReset: '/simulation/reset',
  match: (id: number) => `/matches/${id}`,
} as const
