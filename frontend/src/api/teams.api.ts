import { apiClient } from './client'
import type { Team, CreateTeamDto, UpdateTeamDto } from '../types/team.types'

export const teamsApi = {
    getAll: async (): Promise<Team[]> => {
        const response = await apiClient.get<Team[]>('/teams')
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
