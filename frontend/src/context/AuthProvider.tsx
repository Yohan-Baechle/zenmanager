import { useState, useEffect } from 'react'
import type { ReactNode } from 'react'
import type { User } from '../types/user.types.ts'
import type { LoginDto } from '../types/auth.types.ts'
import { authApi } from '../api/auth.api.ts'
import { usersApi } from '../api/users.api.ts'
import { tokenUtils } from '../utils/token.ts'
import { AuthContext } from './AuthContext.ts'

interface AuthProviderProps {
    children: ReactNode
}

const USER_ID_KEY = 'auth.userId'

export function AuthProvider({ children }: AuthProviderProps) {
    const [user, setUser] = useState<User | null>(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        const initAuth = async () => {
            try {
                const token = tokenUtils.get()
                const storedId = localStorage.getItem(USER_ID_KEY)
                if (token && storedId) {
                    const id = Number(storedId)
                    const currentUser = await usersApi.getById(id)
                    setUser(currentUser)
                } else {
                    tokenUtils.remove()
                    localStorage.removeItem(USER_ID_KEY)
                }
            } catch {
                tokenUtils.remove()
                localStorage.removeItem(USER_ID_KEY)
            } finally {
                setLoading(false)
            }
        }
        initAuth()
    }, [])

    const login = async ({ username, password }: LoginDto) => {
        const { token, user: loginUser } = await authApi.login({ username, password })
        tokenUtils.set(token)
        localStorage.setItem(USER_ID_KEY, String(loginUser.id))
        const currentUser = await usersApi.getById(loginUser.id)
        setUser(currentUser)
    }

    const logout = async () => {
        try {
            await authApi.logout()
        } finally {
            tokenUtils.remove()
            localStorage.removeItem(USER_ID_KEY)
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
