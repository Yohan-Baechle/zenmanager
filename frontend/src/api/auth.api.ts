import { apiClient } from './client'
import type { LoginDto, AuthResponse } from '../types/auth.types'
import type { User } from '../types/user.types'

export const authApi = {
    login: async (credentials: LoginDto): Promise<AuthResponse> => {
        const payload = {
            username: credentials.username,
            password: credentials.password,
        }
        const { data } = await apiClient.post<AuthResponse>('/login_check', payload, {
            headers: { 'Content-Type': 'application/json' },
        })
        return data
    },

    me: async (): Promise<User> => {
        const { data } = await apiClient.get<User>('/me')
        return data
    },

    logout: async (): Promise<void> => {
        await apiClient.post('/logout')
    },
}
