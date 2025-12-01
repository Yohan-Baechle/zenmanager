import { Navigate } from 'react-router-dom'
import { useAuth } from '../../hooks/useAuth'
import type { ReactNode } from 'react'
import Loader from '../common/Loader'

type Role = 'manager' | 'employee' | 'admin'

interface ProtectedRouteProps {
    children: ReactNode
    requiredRole?: Role[]
}

export default function ProtectedRoute({ children, requiredRole }: ProtectedRouteProps) {
    const { isAuthenticated, loading, user } = useAuth()

    if (loading) {
        return <Loader />
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" replace />
    }

    if (requiredRole && requiredRole.length > 0) {
        const role = user?.role as Role | undefined
        if (!role || !requiredRole.includes(role)) {
            return <Navigate to="/dashboard" replace />
        }
    }

    return <>{children}</>
}
