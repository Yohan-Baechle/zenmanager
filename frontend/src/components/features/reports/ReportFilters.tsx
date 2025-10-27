import { useState, useEffect } from 'react'
import { reportsApi } from '../../../api/reports.api'
import type { ReportsFilters, TeamOption, EmployeeOption } from '../../../types/kpi.types'
import { useAuth } from '../../../hooks/useAuth'

interface ReportFiltersProps {
    onApply: (filters: ReportsFilters) => void
    loading?: boolean
}

export default function ReportFilters({ onApply, loading = false }: ReportFiltersProps) {
    const { user } = useAuth()
    const [teams, setTeams] = useState<TeamOption[]>([])
    const [employees, setEmployees] = useState<EmployeeOption[]>([])
    const [loadingTeams, setLoadingTeams] = useState(true)
    const [loadingEmployees, setLoadingEmployees] = useState(false)
    
    const [filters, setFilters] = useState<ReportsFilters>({
        start_date: '',
        end_date: '',
        team_id: undefined,
        user_id: undefined,
    })

    useEffect(() => {
        fetchTeams()
    }, [])

    useEffect(() => {
        if (teams.length > 0 && !filters.team_id) {
            const firstTeamId = teams[0].id
            setFilters(prev => ({ ...prev, team_id: firstTeamId }))
            fetchEmployees(firstTeamId)
            onApply({ team_id: firstTeamId })
        }
    }, [teams])

    useEffect(() => {
        if (filters.team_id) {
            fetchEmployees(filters.team_id)
        }
    }, [filters.team_id])

    const fetchTeams = async () => {
        try {
            const response = await reportsApi.getMyTeams()
            if (response.success) {
                setTeams(response.teams)
            }
        } catch (error) {
            console.error('Error fetching teams:', error)
        } finally {
            setLoadingTeams(false)
        }
    }

    const fetchEmployees = async (teamId: number) => {
        setLoadingEmployees(true)
        try {
            const response = await reportsApi.getTeamEmployees(teamId)
            if (response.success) {
                setEmployees(response.employees)
            }
        } catch (error) {
            console.error('Error fetching employees:', error)
            setEmployees([])
        } finally {
            setLoadingEmployees(false)
        }
    }

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        const { name, value } = e.target

        if (name === 'team_id') {
            setFilters(prev => ({
                ...prev,
                team_id: value === '' ? undefined : parseInt(value),
                user_id: undefined
            }))
        } else {
            setFilters(prev => ({
                ...prev,
                [name]: value === '' ? undefined : 
                        (name === 'user_id') ? parseInt(value) : value
            }))
        }
    }

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault()
        const cleanFilters = Object.fromEntries(
            Object.entries(filters).filter(([_, value]) => value !== undefined && value !== '')
        ) as ReportsFilters
        onApply(cleanFilters)
    }

    const handleReset = () => {
        const resetFilters: ReportsFilters = {
            start_date: '',
            end_date: '',
            team_id: teams.length > 0 ? teams[0].id : undefined,
            user_id: undefined,
        }
        setFilters(resetFilters)
        if (teams.length > 0) {
            fetchEmployees(teams[0].id)
        }
        onApply(resetFilters)
    }

    const isManager = user?.role === 'manager' || user?.role?.includes('ROLE_MANAGER')
    const isAdmin = user?.role === 'admin' || user?.role?.includes('ROLE_ADMIN')

    if (loadingTeams) {
        return (
            <div className="bg-white shadow rounded-lg p-6">
                <div className="flex items-center gap-2">
                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                    <p className="text-gray-600">Chargement des équipes...</p>
                </div>
            </div>
        )
    }

    if (teams.length === 0) {
        return (
            <div className="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                <div className="flex">
                    <div className="flex-shrink-0">
                        <svg className="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                        </svg>
                    </div>
                    <div className="ml-3">
                        <p className="text-sm text-yellow-700">
                            Aucune équipe disponible. Veuillez contacter votre administrateur.
                        </p>
                    </div>
                </div>
            </div>
        )
    }

    const hasMultipleTeams = teams.length > 1

    return (
        <div className="bg-white shadow rounded-lg p-6">
            <h2 className="text-lg font-semibold mb-4">Filtres</h2>
            <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Date de début
                        </label>
                        <input
                            type="date"
                            name="start_date"
                            value={filters.start_date || ''}
                            onChange={handleChange}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Date de fin
                        </label>
                        <input
                            type="date"
                            name="end_date"
                            value={filters.end_date || ''}
                            onChange={handleChange}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    {hasMultipleTeams ? (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Équipe {isManager && <span className="text-red-500">*</span>}
                            </label>
                            <select
                                name="team_id"
                                value={filters.team_id || ''}
                                onChange={handleChange}
                                required
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            >
                                {teams.map(team => (
                                    <option key={team.id} value={team.id}>
                                        {team.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    ) : (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Votre équipe
                            </label>
                            <div className="relative">
                                <input
                                    type="text"
                                    value={teams[0]?.name || ''}
                                    disabled
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600 cursor-not-allowed"
                                />
                                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg className="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    )}

                    {(isManager || isAdmin) && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Employé (optionnel)
                            </label>
                            <select
                                name="user_id"
                                value={filters.user_id || ''}
                                onChange={handleChange}
                                disabled={loadingEmployees}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white disabled:bg-gray-100 disabled:cursor-not-allowed"
                            >
                                <option value="">Tous les employés</option>
                                {loadingEmployees ? (
                                    <option disabled>Chargement...</option>
                                ) : (
                                    employees.map(employee => (
                                        <option key={employee.id} value={employee.id}>
                                            {employee.fullName}
                                        </option>
                                    ))
                                )}
                            </select>
                            {loadingEmployees && (
                                <p className="text-xs text-gray-500 mt-1">
                                    Chargement des employés...
                                </p>
                            )}
                        </div>
                    )}
                </div>

                <div className="flex gap-3 mt-4">
                    <button
                        type="submit"
                        disabled={loading}
                        className="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors font-medium"
                    >
                        {loading ? (
                            <span className="flex items-center gap-2">
                                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                Chargement...
                            </span>
                        ) : (
                            'Appliquer les filtres'
                        )}
                    </button>
                    <button
                        type="button"
                        onClick={handleReset}
                        disabled={loading}
                        className="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors font-medium"
                    >
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
    )
}
