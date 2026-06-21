import type { BaseResponse } from '@/core/models/BaseResponse'

export interface IApiService {
  get<T>(path: string): Promise<BaseResponse<T>>
  post<T>(path: string, body?: unknown): Promise<BaseResponse<T>>
  patch<T>(path: string, body?: unknown): Promise<BaseResponse<T>>
}
