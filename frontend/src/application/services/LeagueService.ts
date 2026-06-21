import type { BaseResponse } from '@/core/models/BaseResponse'
import type { StandingRow } from '@/application/models/StandingRow'
import type { Prediction } from '@/application/models/Prediction'
import { ApiPaths } from '@/core/constants/ApiPaths'
import apiClient from '@/infrastructure/client/DefaultClient'

export async function getLeagueTable(): Promise<StandingRow[]> {
  const { data } = await apiClient.get<BaseResponse<StandingRow[]>>(ApiPaths.leagueTable)
  return data.data
}

export async function getPredictions(): Promise<Prediction[] | null> {
  const { data } = await apiClient.get<BaseResponse<Prediction[] | null>>(ApiPaths.predictions)
  return data.data
}
