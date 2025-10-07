import { apiClient } from './client'

export interface KPIReport {
    type: string
    value: number
    label: string
    period: string
}

export const reportsApi = {
    getGlobalReport: async (params?: {
        startDate?: string
        endDate?: string
        teamId?: number
    }): Promise<KPIReport[]> => {
        const response = await apiClient.get<KPIReport[]>('/reports', { params })
        return response.data
    },

    getTeamReport: async (teamId: number, params?: {
        startDate?: string
        endDate?: string
    }): Promise<any> => {
        const response = await apiClient.get(`/reports/teams/${teamId}`, { params })
        return response.data
    },
}
