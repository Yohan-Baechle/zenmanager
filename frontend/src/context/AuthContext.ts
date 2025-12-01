import { createContext } from 'react'
import type { User } from '../types/user.types.ts'
import type { LoginDto } from '../types/auth.types.ts'

interface AuthContextType {
    user: User | null
    loading: boolean
    login: (credentials: LoginDto) => Promise<void>
    logout: () => Promise<void>
    isAuthenticated: boolean
    role?: string
}

export const AuthContext = createContext<AuthContextType | undefined>(undefined)
