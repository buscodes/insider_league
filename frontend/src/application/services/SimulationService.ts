import type { BaseResponse } from '@/core/models/BaseResponse'
import type { Match } from '@/application/models/Match'
import { ApiPaths } from '@/core/constants/ApiPaths'
import apiClient from '@/infrastructure/client/DefaultClient'

export async function playNextWeek(): Promise<Match[]> {
  const { data } = await apiClient.post<BaseResponse<Match[]>>(ApiPaths.simulationPlayWeek)
  return data.data
}

export async function playAll(): Promise<Match[]> {
  const { data } = await apiClient.post<BaseResponse<Match[]>>(ApiPaths.simulationPlayAll)
  return data.data
}

export async function resetLeague(): Promise<void> {
  await apiClient.post(ApiPaths.simulationReset)
}

export async function updateMatchScore(
  id: number,
  homeScore: number,
  awayScore: number,
): Promise<Match> {
  const { data } = await apiClient.patch<BaseResponse<Match>>(ApiPaths.match(id), {
    home_score: homeScore,
    away_score: awayScore,
  })
  return data.data
}
