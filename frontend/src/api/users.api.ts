import { apiClient } from './client'
import type { User, CreateUserDto, UpdateUserDto } from '../types/user.types'

export const usersApi = {
    getAll: async (): Promise<User[]> => {
        const response = await apiClient.get<User[]>('/users')
        return response.data
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
}
