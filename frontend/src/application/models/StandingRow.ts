export interface StandingRow {
  team_name: string
  points: number
  played: number
  won: number
  drawn: number
  lost: number
  goals_for: number
  goals_against: number
  goal_difference: number
  championship_prediction: number | null
}
