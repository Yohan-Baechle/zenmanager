import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { teamsApi } from '../../api/teams.api'
import type { Team, UpdateTeamDto, CreateTeamDto } from '../../types/team.types'
import TeamForm from '../../components/features/teams/TeamForm'
import Card from '../../components/common/Card'
import Loader from '../../components/common/Loader'

export default function EditTeamPage() {
    const { id } = useParams<{ id: string }>()
    const [team, setTeam] = useState<Team | null>(null)
    const [loading, setLoading] = useState(true)
    const navigate = useNavigate()

    useEffect(() => {
        loadTeam()
    }, [id])

    const loadTeam = async () => {
        try {
            const data = await teamsApi.getById(Number(id))
            setTeam(data)
        } catch (error) {
            alert(`Failed to load team: ${error instanceof Error ? error.message : 'Unknown error'}`)
        } finally {
            setLoading(false)
        }
    }

    const handleSubmit = async (data: CreateTeamDto | UpdateTeamDto) => {
        try {
            await teamsApi.update(Number(id), data as UpdateTeamDto)
            navigate('/teams')
        } catch (error) {
            alert(`Failed to update team: ${error instanceof Error ? error.message : 'Unknown error'}`)
        }
    }

    if (loading) return <Loader />
    if (!team) return <div>Team not found</div>

    return (
        <div className="max-w-2xl">
            <h1 className="text-2xl font-bold mb-6">Edit Team</h1>
            <Card>
                <TeamForm onSubmit={handleSubmit} initialData={team} isEdit />
            </Card>
        </div>
    )
}
