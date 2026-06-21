import type { BaseMeta } from './BaseMeta'

export interface BaseResponse<T> {
  success: boolean
  message: string
  data: T
  meta: BaseMeta
}
