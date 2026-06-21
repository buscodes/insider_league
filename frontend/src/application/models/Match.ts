import type { Team } from './Team'

export interface Match {
  id: number
  week: number
  home_team: Team
  away_team: Team
  home_score: number | null
  away_score: number | null
  is_played: boolean
}
