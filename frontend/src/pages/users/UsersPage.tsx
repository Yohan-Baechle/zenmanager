import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { toast } from 'sonner'
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
            toast.error('Échec du chargement des utilisateurs')
        } finally {
            setLoading(false)
        }
    }

    const handleEdit = (id: number) => {
        navigate(`/users/edit/${id}`)
    }

    const handleDelete = async (id: number) => {
        if (window.confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')) {
            try {
                await usersApi.delete(id)
                toast.success('Utilisateur supprimé avec succès!')
                loadUsers()
            } catch (error) {
                toast.error(`Échec de la suppression de l'utilisateur: ${error instanceof Error ? error.message : 'Erreur inconnue'}`)
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
