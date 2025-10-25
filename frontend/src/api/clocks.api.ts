import { apiClient } from './client'
import type { Clock, ClockRequestDto, CreateClockDto } from '../types/clock.types'

export const clocksApi = {
    create: async (data: CreateClockDto): Promise<Clock> => {
        const response = await apiClient.post<Clock>('/clocks', data)
        return response.data
    },

    getUserClocks: async (userId: number): Promise<Clock[]> => {
        const response = await apiClient.get<Clock[]>(`/clocks/${userId}`)
        return response.data
    },

    getClocksRequest: async (data?: ClockRequestDto): Promise<Clock[]> => {
        const response = await apiClient.get<Clock[]>(`/clock-requests`, { params: data })
        return response.data
    },

    postClockRequest: async (data: Partial<ClockRequestDto>): Promise<ClockRequestDto> => {
        const response = await apiClient.post<ClockRequestDto>('/clock-requests', data)
        return response.data
    },

    updateClockRequest: async (id: number, data: Partial<ClockRequestDto>): Promise<ClockRequestDto> => {
        const response = await apiClient.patch<ClockRequestDto>(`/clock-requests/${id}`, data)
        return response.data
    },
}
