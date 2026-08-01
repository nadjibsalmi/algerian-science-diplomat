export type UserRole = 'admin' | 'diplomat' | 'researcher' | 'guest'

export interface User {
  id: string
  email: string
  firstName: string
  lastName: string
  role: UserRole
  locale: string
  avatarUrl?: string
  institution?: string
  country?: string
  createdAt: string
  updatedAt: string
}

export interface AuthState {
  user: User | null
  accessToken: string | null
  isAuthenticated: boolean
  isLoading: boolean
}
