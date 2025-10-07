import type { User } from '../../../types/user.types'
import Table from '../../common/Table'
import Button from '../../common/Button'

interface UserListProps {
    users: User[]
    onEdit: (id: number) => void
    onDelete: (id: number) => void
}

export default function UserList({ users, onEdit, onDelete }: UserListProps) {
    const columns = [
        { header: 'Name', accessor: (user: User) => `${user.firstName} ${user.lastName}` },
        { header: 'Email', accessor: 'email' as keyof User },
        { header: 'Phone', accessor: 'phoneNumber' as keyof User },
        { header: 'Role', accessor: 'role' as keyof User },
        {
            header: 'Actions',
            accessor: (user: User) => (
                <div className="flex gap-2">
                    <Button variant="secondary" onClick={() => onEdit(user.id)}>
                        Edit
                    </Button>
                    <Button variant="danger" onClick={() => onDelete(user.id)}>
                        Delete
                    </Button>
                </div>
            ),
        },
    ]

    return <Table data={users} columns={columns} />
}
