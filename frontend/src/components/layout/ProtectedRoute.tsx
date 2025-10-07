import { Navigate } from 'react-router-dom'
import { useAuth } from '../../hooks/useAuth'
import type { ReactNode } from 'react'
import Loader from '../common/Loader'

interface ProtectedRouteProps {
    children: ReactNode
    requiredRole?: 'manager' | 'employee'
}

export default function ProtectedRoute({ children, requiredRole }: ProtectedRouteProps) {
    const { isAuthenticated, loading, user } = useAuth()

    if (loading) {
        return <Loader />
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" replace />
    }

    if (requiredRole && user?.role !== requiredRole) {
        return <Navigate to="/dashboard" replace />
    }

    return <>{children}</>
}
