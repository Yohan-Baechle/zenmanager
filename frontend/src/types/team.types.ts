import type { User } from './user.types'

export interface Team {
    id: number
    name: string
    description: string
    manager?: User
    employees?: User[]
    createdAt: string
    updatedAt: string
}

export interface CreateTeamDto {
    name: string
    description: string
    managerId: number
}

export interface UpdateTeamDto {
    name?: string
    description?: string
    managerId?: number
}
