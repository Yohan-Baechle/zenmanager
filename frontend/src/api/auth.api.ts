import { apiClient } from './client'
import type { LoginDto, AuthResponse } from '../types/auth.types'

export const authApi = {
    login: async (credentials: LoginDto): Promise<AuthResponse> => {
        const response = await apiClient.post<AuthResponse>('/auth/login', credentials)
        return response.data
    },

    logout: async (): Promise<void> => {
        await apiClient.post('/auth/logout')
    },

    getCurrentUser: async () => {
        const response = await apiClient.get('/auth/me')
        return response.data
    },
}
