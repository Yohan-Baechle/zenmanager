import type { Team } from '../../../types/team.types'
import Table from '../../common/Table'
import { EditSquareIcon } from "../../../assets/icons/edit-square.tsx"
import { DeleteForeverIcon } from "../../../assets/icons/delete-forever.tsx"

interface TeamListProps {
    teams: Team[]
    onEdit: (id: number) => void
    onDelete: (id: number) => void
}

export default function TeamList({ teams, onEdit, onDelete }: TeamListProps) {
    const columns = [
        { header: 'Nom', accessor: 'name' as keyof Team },
        {
            header: 'Description',
            accessor: (team: Team) => {
                const desc = team.description || ''
                return desc.length > 30 ? `${desc.substring(0, 30)}...` : desc
            }
        },
        {
            header: 'Manager',
            accessor: (team: Team) => team.manager
                ? `${team.manager.firstName} ${team.manager.lastName}`
                : 'Aucun'
        },
        {
            header: 'Employés',
            accessor: (team: Team) => team.employees?.length || 0
        },
        {
            header: 'Actions',
            accessor: (team: Team) => (
                <div className="flex gap-2">
                    <EditSquareIcon
                        className="h-7 w-7 p-1 cursor-pointer hover:bg-[var(--c1)] rounded"
                        onClick={() => onEdit(team.id)} />
                    <DeleteForeverIcon
                        className="h-7 w-7 p-1 cursor-pointer hover:bg-[var(--c1)] rounded"
                        onClick={() => onDelete(team.id)} />
                </div>
            ),
        },
    ]

    return <Table data={teams} columns={columns} />
}
