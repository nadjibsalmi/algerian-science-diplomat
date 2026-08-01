import type { Ref } from 'vue'

export type Nullable<T> = T | null
export type Optional<T> = T | undefined
export type MaybeRef<T> = T | Ref<T>

export interface SelectOption<T = string> {
  label: string
  value: T
  disabled?: boolean
}

export type Size = 'xs' | 'sm' | 'md' | 'lg' | 'xl'

export type Variant =
  | 'primary'
  | 'secondary'
  | 'success'
  | 'warning'
  | 'danger'
  | 'ghost'
  | 'outline'

export type ColorScheme = 'light' | 'dark' | 'system'
