import type { User } from '../../../types/user.types'
import Table from '../../common/Table'
import {EditSquareIcon} from "../../../assets/icons/edit-square.tsx";
import {DeleteForeverIcon} from "../../../assets/icons/delete-forever.tsx";

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
                user.role === 'admin' ? (
                    <span className="text-gray-500 text-sm">Impossible</span>
                ) : (
                    <div className="flex gap-2">
                        <EditSquareIcon
                            className="h-7 w-7 p-1 cursor-pointer hover:bg-[var(--c1)] rounded"
                            onClick={() => onEdit(user.id)} />
                        <DeleteForeverIcon
                            className="h-7 w-7 p-1 cursor-pointer hover:bg-[var(--c1)] rounded"
                            onClick={() => onDelete(user.id)} />
                    </div>
                )
            ),
        },
    ]

    return <Table data={users} columns={columns} />
}
