import { useState, useEffect } from 'react'
import { reportsApi } from '../../../api/reports.api'
import type { ReportsFilters, TeamOption, EmployeeOption } from '../../../types/kpi.types'
import { useAuth } from '../../../hooks/useAuth'
import Card from '../../common/Card'
import Input from '../../common/Input'
import Select from '../../common/Select'
import Button from '../../common/Button'
import Loader from '../../common/Loader'

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
        // eslint-disable-next-line react-hooks/exhaustive-deps
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
            Object.entries(filters).filter(([, value]) => value !== undefined && value !== '')
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
            <Card>
                <div className="flex items-center justify-center gap-4">
                    <Loader />
                    <p className="text-[var(--c4)]">Chargement des équipes...</p>
                </div>
            </Card>
        )
    }

    if (teams.length === 0) {
        return (
            <Card className="bg-yellow-50 border-yellow-300">
                <div className="flex items-start gap-3">
                    <span className="text-2xl">⚠️</span>
                    <div>
                        <p className="font-bold text-yellow-800">Aucune équipe disponible</p>
                        <p className="text-yellow-700 mt-1">
                            Veuillez contacter votre administrateur.
                        </p>
                    </div>
                </div>
            </Card>
        )
    }

    const hasMultipleTeams = teams.length > 1

    // Préparer les options pour les selects
    const teamOptions = teams.map(team => ({
        value: String(team.id),
        label: team.name
    }))

    const employeeOptions = [
        { value: '', label: 'Tous les employés' },
        ...employees.map(employee => ({
            value: String(employee.id),
            label: employee.fullName
        }))
    ]

    return (
        <Card title="🔍 Filtres">
            <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Input
                        type="date"
                        name="start_date"
                        label="Date de début"
                        value={filters.start_date || ''}
                        onChange={handleChange}
                        floatingLabel
                    />

                    <Input
                        type="date"
                        name="end_date"
                        label="Date de fin"
                        value={filters.end_date || ''}
                        onChange={handleChange}
                        floatingLabel
                    />

                    {hasMultipleTeams ? (
                        <Select
                            name="team_id"
                            label={`Équipe${isManager ? ' *' : ''}`}
                            options={teamOptions}
                            value={String(filters.team_id || '')}
                            onChange={handleChange}
                            required
                            floatingLabel
                        />
                    ) : (
                        <Input
                            type="text"
                            label="Votre équipe"
                            value={teams[0]?.name || ''}
                            disabled
                            floatingLabel
                        />
                    )}

                    {(isManager || isAdmin) && (
                        <div className="relative">
                            <Select
                                name="user_id"
                                label="Employé (optionnel)"
                                options={employeeOptions}
                                value={String(filters.user_id || '')}
                                onChange={handleChange}
                                disabled={loadingEmployees}
                                floatingLabel
                            />
                            {loadingEmployees && (
                                <div className="absolute top-1/2 right-12 -translate-y-1/2">
                                    <div className="w-4 h-4 border-2 border-[var(--c4)] border-t-transparent rounded-full animate-spin"></div>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                <div className="flex gap-3 mt-6">
                    <Button type="submit" variant="primary" disabled={loading}>
                        {loading ? (
                            <span className="flex items-center gap-2">
                                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Chargement...
                            </span>
                        ) : (
                            '✓ Appliquer les filtres'
                        )}
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={handleReset}
                        disabled={loading}
                    >
                        ↻ Réinitialiser
                    </Button>
                </div>
            </form>
        </Card>
    )
}
