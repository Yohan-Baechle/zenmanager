import type { Team } from './team.types'

export type UserRole = 'employee' | 'manager' | 'admin'

export interface User {
    id: number
    username: string
    email: string
    firstName: string
    lastName: string
    phoneNumber?: string
    role: UserRole
    team?: Team | null
    createdAt?: string
    updatedAt?: string
}

export interface CreateUserDto {
    username: string
    firstName: string
    lastName: string
    email: string
    phoneNumber: string
    role: UserRole
    teamId?: number
}

export interface UpdateUserDto {
    username?: string
    firstName?: string
    lastName?: string
    email?: string
    phoneNumber?: string
    password?: string
    role?: UserRole
    teamId?: number
}
