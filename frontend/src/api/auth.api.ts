import { apiClient } from './client'
import type { LoginDto, AuthResponse } from '../types/auth.types'

export const authApi = {
    login: async (credentials: LoginDto): Promise<AuthResponse> => {
        const payload = {
            username: credentials.username,
            password: credentials.password,
        }
        const response = await apiClient.post<AuthResponse>('/login_check', payload, {
            headers: { 'Content-Type': 'application/json' },
        })
        return response.data
    },

    logout: async (): Promise<void> => {
        await apiClient.post('/logout')
    },

    getCurrentUser: async () => {
        const response = await apiClient.get('/me')
        return response.data
    },
}
