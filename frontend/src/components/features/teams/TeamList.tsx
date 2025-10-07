import type { Team } from '../../../types/team.types'
import Table from '../../common/Table'
import Button from '../../common/Button'

interface TeamListProps {
    teams: Team[]
    onEdit: (id: number) => void
    onDelete: (id: number) => void
}

export default function TeamList({ teams, onEdit, onDelete }: TeamListProps) {
    const columns = [
        { header: 'Name', accessor: 'name' as keyof Team },
        { header: 'Description', accessor: 'description' as keyof Team },
        { header: 'Members', accessor: (team: Team) => team.memberIds.length },
        {
            header: 'Actions',
            accessor: (team: Team) => (
                <div className="flex gap-2">
                    <Button variant="secondary" onClick={() => onEdit(team.id)}>
                        Edit
                    </Button>
                    <Button variant="danger" onClick={() => onDelete(team.id)}>
                        Delete
                    </Button>
                </div>
            ),
        },
    ]

    return <Table data={teams} columns={columns} />
}
