export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

export const ROUTES = {
    LOGIN: '/login',
    DASHBOARD: '/dashboard',
    MANAGER_DASHBOARD: '/manager/dashboard',
    CLOCK: '/clock',
    USERS: '/users',
    TEAMS: '/teams',
    REPORTS: '/reports',
    PROFILE: '/profile',
    ADMIN: '/admin',
} as const
