import { useNavigate } from 'react-router-dom'
import { teamsApi } from '../../api/teams.api'
import type { CreateTeamDto, UpdateTeamDto } from '../../types/team.types'
import TeamForm from '../../components/features/teams/TeamForm'
import Card from '../../components/common/Card'

export default function CreateTeamPage() {
    const navigate = useNavigate()

    const handleSubmit = async (data: CreateTeamDto | UpdateTeamDto) => {
        try {
            await teamsApi.create(data as CreateTeamDto)
            navigate('/teams')
        } catch (error) {
            alert(`Failed to create team: ${error instanceof Error ? error.message : 'Unknown error'}`)
        }
    }

    return (
        <div className="max-w-2xl">
            <h1 className="text-2xl font-bold mb-6">Create Team</h1>
            <Card>
                <TeamForm onSubmit={handleSubmit} />
            </Card>
        </div>
    )
}
