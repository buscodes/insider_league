import type { AxiosInstance } from 'axios'
import type { BaseResponse } from '@/core/models/BaseResponse'
import type { IApiService } from './IApiService'
import apiClient from '@/infrastructure/client/DefaultClient'

export class ApiService implements IApiService {
  private readonly client: AxiosInstance = apiClient

  async get<T>(path: string): Promise<BaseResponse<T>> {
    const { data } = await this.client.get<BaseResponse<T>>(path)
    return data
  }

  async post<T>(path: string, body?: unknown): Promise<BaseResponse<T>> {
    const { data } = await this.client.post<BaseResponse<T>>(path, body)
    return data
  }

  async patch<T>(path: string, body?: unknown): Promise<BaseResponse<T>> {
    const { data } = await this.client.patch<BaseResponse<T>>(path, body)
    return data
  }
}

export const apiService = new ApiService()
