import { apiClient } from './client'
import type { Clock, CreateClockDto, WorkingHoursSummary } from '../types/clock.types'

export const clocksApi = {
    create: async (data: CreateClockDto): Promise<Clock> => {
        const response = await apiClient.post<Clock>('/clocks', data)
        return response.data
    },

    getUserClocks: async (userId: number): Promise<Clock[]> => {
        const response = await apiClient.get<Clock[]>(`/users/${userId}/clocks`)
        return response.data
    },

    getWorkingSummary: async (userId: number): Promise<WorkingHoursSummary> => {
        const response = await apiClient.get<WorkingHoursSummary>(`/users/${userId}/working-hours`)
        return response.data
    },
}
