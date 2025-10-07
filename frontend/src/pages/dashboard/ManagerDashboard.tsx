import { useEffect, useState } from 'react'
import { teamsApi } from '../../api/teams.api'
import type { Team } from '../../types/team.types'
import KPICard from '../../components/features/reports/KPICard'
import TeamCard from '../../components/features/teams/TeamCard'
import Loader from '../../components/common/Loader'

export default function ManagerDashboard() {
    const [teams, setTeams] = useState<Team[]>([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        loadTeams()
    }, [])

    const loadTeams = async () => {
        try {
            const data = await teamsApi.getAll()
            setTeams(data)
        } catch (error) {
            console.error('Failed to load teams', error)
        } finally {
            setLoading(false)
        }
    }

    if (loading) return <Loader />

    return (
        <div className="space-y-6">
            <h1 className="text-2xl font-bold">Manager Dashboard</h1>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <KPICard
                    label="Total Teams"
                    value={teams.length}
                    icon="👥"
                    color="blue"
                />
                <KPICard
                    label="Total Members"
                    value={teams.reduce((acc, team) => acc + team.memberIds.length, 0)}
                    icon="👤"
                    color="green"
                />
                <KPICard
                    label="Active Projects"
                    value="0"
                    icon="📋"
                    color="purple"
                />
            </div>

            <div>
                <h2 className="text-xl font-semibold mb-4">My Teams</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {teams.map((team) => (
                        <TeamCard key={team.id} team={team} />
                    ))}
                </div>
            </div>
        </div>
    )
}
