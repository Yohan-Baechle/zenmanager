import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { teamsApi } from '../../api/teams.api'
import type { Team } from '../../types/team.types'
import TeamList from '../../components/features/teams/TeamList'
import Button from '../../components/common/Button'
import Loader from '../../components/common/Loader'

export default function TeamsPage() {
    const [teams, setTeams] = useState<Team[]>([])
    const [loading, setLoading] = useState(true)
    const navigate = useNavigate()

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

    const handleEdit = (id: number) => {
        navigate(`/teams/edit/${id}`)
    }

    const handleDelete = async (id: number) => {
        if (window.confirm('Are you sure you want to delete this team?')) {
            try {
                await teamsApi.delete(id)
                loadTeams()
            } catch (error) {
                alert(`Failed to delete team: ${error instanceof Error ? error.message : 'Unknown error'}`)
            }
        }
    }

    if (loading) return <Loader />

    return (
        <div className="space-y-4">
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold">Teams</h1>
                <Button onClick={() => navigate('/teams/create')}>Create Team</Button>
            </div>
            <TeamList teams={teams} onEdit={handleEdit} onDelete={handleDelete} />
        </div>
    )
}
