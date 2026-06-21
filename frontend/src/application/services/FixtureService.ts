import type { BaseResponse } from '@/core/models/BaseResponse'
import type { Match } from '@/application/models/Match'
import { ApiPaths } from '@/core/constants/ApiPaths'
import apiClient from '@/infrastructure/client/DefaultClient'

export async function getFixtures(): Promise<Match[]> {
  const { data } = await apiClient.get<BaseResponse<Match[]>>(ApiPaths.fixtures)
  return data.data
}

export async function generateFixtures(): Promise<Match[]> {
  const { data } = await apiClient.post<BaseResponse<Match[]>>(ApiPaths.fixturesGenerate)
  return data.data
}
