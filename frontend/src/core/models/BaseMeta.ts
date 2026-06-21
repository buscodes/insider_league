import type { PaginationMeta } from './PaginationMeta'

export interface BaseMeta {
  timestamp: string
  version: string
  pagination: PaginationMeta | null
}
