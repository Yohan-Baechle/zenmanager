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
            try {
                const token = tokenUtils.get()
                if (token) {
                    const currentUser = await authApi.me()
                    setUser(currentUser)
                } else {
                    tokenUtils.remove()
                }
            } catch {
                tokenUtils.remove()
            } finally {
                setLoading(false)
            }
        }
        initAuth()
    }, [])

    const login = async ({ username, password }: LoginDto) => {
        const { token } = await authApi.login({ username, password })
        tokenUtils.set(token)
        const currentUser = await authApi.me()
        setUser(currentUser)
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
