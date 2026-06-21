import type { BaseResponse } from '@/core/models/BaseResponse'
import type { Team } from '@/application/models/Team'
import { ApiPaths } from '@/core/constants/ApiPaths'
import apiClient from '@/infrastructure/client/DefaultClient'

export async function getTeams(): Promise<Team[]> {
  const { data } = await apiClient.get<BaseResponse<Team[]>>(ApiPaths.teams)
  return data.data
}
