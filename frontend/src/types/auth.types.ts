import type { User } from './user.types'

export interface LoginDto {
    email: string
    password: string
}

export interface AuthResponse {
    token: string
    user: User
}
