import { apiClient } from './client'
import type { User, CreateUserDto, UpdateUserDto } from '../types/user.types'

interface PaginatedResponse<T> {
    data: T[]
    meta: {
        currentPage: number
        itemsPerPage: number
        totalItems: number
        totalPages: number
    }
}

export const usersApi = {
    getAll: async (): Promise<User[]> => {
        const response = await apiClient.get<PaginatedResponse<User>>('/users')
        return response.data.data
    },

    getById: async (id: number): Promise<User> => {
        const response = await apiClient.get<User>(`/users/${id}`)
        return response.data
    },

    create: async (data: CreateUserDto): Promise<User> => {
        const response = await apiClient.post<User>('/users', data)
        return response.data
    },

    update: async (id: number, data: UpdateUserDto): Promise<User> => {
        const response = await apiClient.put<User>(`/users/${id}`, data)
        return response.data
    },

    delete: async (id: number): Promise<void> => {
        await apiClient.delete(`/users/${id}`)
    },

    getClocks: async (id: number, start?: string, end?: string): Promise<unknown> => {
        const params: Record<string, string> = {}
        if (start) params.start = start
        if (end) params.end = end

        const response = await apiClient.get<unknown>(`/users/${id}/clocks`, { params })
        return response.data
    },

    regeneratePassword: async (id: number): Promise<void> => {
        const response = await apiClient.post(`/users/${id}/regenerate-password`)
        return response.data
    },
}
