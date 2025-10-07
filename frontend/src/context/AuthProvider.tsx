import { useState, useEffect } from 'react'
import type { ReactNode } from 'react'
import type { User } from '../types/user.types.ts'
import type { LoginDto } from '../types/auth.types.ts'
import { authApi } from '../api/auth.api.ts'
import { tokenUtils } from '../utils/token.ts'
import { AuthContext } from './AuthContext.ts'

interface AuthProviderProps {
    children: ReactNode
}

export function AuthProvider({ children }: AuthProviderProps) {
    const [user, setUser] = useState<User | null>(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        const initAuth = async () => {
            const token = tokenUtils.get()
            if (token && !tokenUtils.isExpired(token)) {
                try {
                    const currentUser = await authApi.getCurrentUser()
                    setUser(currentUser)
                } catch (error) {
                    console.error('Failed to initialize auth:', error)
                    tokenUtils.remove()
                }
            }
            setLoading(false)
        }

        initAuth()
    }, [])

    const login = async (credentials: LoginDto) => {
        const { token, user: userData } = await authApi.login(credentials)
        tokenUtils.set(token)
        setUser(userData)
    }

    const logout = async () => {
        try {
            await authApi.logout()
        } finally {
            tokenUtils.remove()
            setUser(null)
        }
    }

    const value = {
        user,
        loading,
        login,
        logout,
        isAuthenticated: !!user,
        isManager: user?.role === 'manager',
    }

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
