export interface Team {
    id: number
    name: string
    description: string
    managerId: number
    memberIds: number[]
    createdAt: string
    updatedAt: string
}

export interface CreateTeamDto {
    name: string
    description: string
    managerId: number
    memberIds: number[]
}

export interface UpdateTeamDto {
    name?: string
    description?: string
    managerId?: number
    memberIds?: number[]
}
