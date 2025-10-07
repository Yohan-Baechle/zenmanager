import type { Team } from '../../../types/team.types'
import Card from '../../common/Card'

interface TeamCardProps {
    team: Team
}

export default function TeamCard({ team }: TeamCardProps) {
    return (
        <Card>
            <div className="space-y-2">
                <p className="text-lg font-semibold">{team.name}</p>
                <p className="text-sm text-gray-600">{team.description}</p>
                <p className="text-sm text-gray-500">
                    Members: {team.memberIds.length}
                </p>
            </div>
        </Card>
    )
}
