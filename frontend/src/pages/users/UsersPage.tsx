import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { usersApi } from '../../api/users.api'
import type { User } from '../../types/user.types'
import UserList from '../../components/features/users/UserList'
import Button from '../../components/common/Button'
import Loader from '../../components/common/Loader'

export default function UsersPage() {
    const [users, setUsers] = useState<User[]>([])
    const [loading, setLoading] = useState(true)
    const navigate = useNavigate()

    useEffect(() => {
        loadUsers()
    }, [])

    const loadUsers = async () => {
        try {
            const data = await usersApi.getAll()
            setUsers(data)
        } catch (error) {
            console.error('Failed to load users', error)
        } finally {
            setLoading(false)
        }
    }

    const handleEdit = (id: number) => {
        navigate(`/users/edit/${id}`)
    }

    const handleDelete = async (id: number) => {
        if (window.confirm('Are you sure you want to delete this user?')) {
            try {
                await usersApi.delete(id)
                loadUsers()
            } catch (error) {
                alert(`Failed to delete user: ${error instanceof Error ? error.message : 'Unknown error'}`)
            }
        }
    }

    if (loading) return <Loader />

    return (
        <div className="space-y-4">
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold">Users</h1>
                <Button onClick={() => navigate('/users/create')}>Create User</Button>
            </div>
            <UserList users={users} onEdit={handleEdit} onDelete={handleDelete} />
        </div>
    )
}
