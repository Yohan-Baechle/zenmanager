import { apiClient } from './client'
import type { Team, CreateTeamDto, UpdateTeamDto } from '../types/team.types'

interface PaginatedResponse<T> {
    data: T[]
    meta: {
        currentPage: number
        itemsPerPage: number
        totalItems: number
        totalPages: number
    }
}

export const teamsApi = {
    getAll: async (page: number = 1, limit: number = 20): Promise<PaginatedResponse<Team>> => {
        const response = await apiClient.get<PaginatedResponse<Team>>('/teams', {
            params: { page, limit }
        })
        return response.data
    },

    getById: async (id: number): Promise<Team> => {
        const response = await apiClient.get<Team>(`/teams/${id}`)
        return response.data
    },

    create: async (data: CreateTeamDto): Promise<Team> => {
        const response = await apiClient.post<Team>('/teams', data)
        return response.data
    },

    update: async (id: number, data: UpdateTeamDto): Promise<Team> => {
        const response = await apiClient.put<Team>(`/teams/${id}`, data)
        return response.data
    },

    delete: async (id: number): Promise<void> => {
        await apiClient.delete(`/teams/${id}`)
    },
}
