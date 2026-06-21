import type { AxiosError } from 'axios'
import type { BaseResponse } from '@/core/models/BaseResponse'
import type { ValidationErrorBag } from '@/core/models/ValidationErrorBag'
import type { ApiError } from '@/core/models/ApiError'
import { Value } from '@/core/constants/Value'
import apiClient from '@/infrastructure/client/DefaultClient'

apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<BaseResponse<ValidationErrorBag | null>>) => {
    const status = error.response?.status ?? Value.ZERO
    const body = error.response?.data

    const normalized: ApiError = {
      message: body?.message ?? error.message,
      status,
      errors: (body?.data as ValidationErrorBag | null)?.errors ?? undefined,
    }

    return Promise.reject(normalized)
  },
)
