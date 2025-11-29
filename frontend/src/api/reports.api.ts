import { apiClient } from './client'
import type { ReportsResponse, ReportsFilters, MyTeamsResponse, TeamEmployeesResponse } from '../types/kpi.types'

export const reportsApi = {
    getReports: async (filters?: ReportsFilters): Promise<ReportsResponse> => {
        const params: Record<string, string> = {}
        
        if (filters?.start_date) params.start_date = filters.start_date
        if (filters?.end_date) params.end_date = filters.end_date
        if (filters?.team_id) params.team_id = filters.team_id.toString()
        if (filters?.user_id) params.user_id = filters.user_id.toString()

        const response = await apiClient.get<ReportsResponse>('/reports', { params })
        return response.data
    },

    getMyTeams: async (): Promise<MyTeamsResponse> => {
        const response = await apiClient.get<MyTeamsResponse>('/reports/my-teams')
        return response.data
    },

    getTeamEmployees: async (teamId: number): Promise<TeamEmployeesResponse> => {
        const response = await apiClient.get<TeamEmployeesResponse>(`/reports/team/${teamId}/employees`)
        return response.data
    }
}
