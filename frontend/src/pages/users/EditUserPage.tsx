import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { toast } from 'sonner'
import { usersApi } from '../../api/users.api'
import type { User, UpdateUserDto, CreateUserDto } from '../../types/user.types'
import UserForm from '../../components/features/users/UserForm'
import Card from '../../components/common/Card'
import Loader from '../../components/common/Loader'

export default function EditUserPage() {
    const { id } = useParams<{ id: string }>()
    const [user, setUser] = useState<User | null>(null)
    const [loading, setLoading] = useState(true)
    const navigate = useNavigate()

    useEffect(() => {
        loadUser()
    }, [id])

    const loadUser = async () => {
        try {
            const data = await usersApi.getById(Number(id))
            setUser(data)
        } catch (error) {
            toast.error(`Échec du chargement de l'utilisateur: ${error instanceof Error ? error.message : 'Erreur inconnue'}`)
        } finally {
            setLoading(false)
        }
    }

    const handleSubmit = async (data: CreateUserDto | UpdateUserDto) => {
        try {
            await usersApi.update(Number(id), data as UpdateUserDto)
            toast.success('Utilisateur modifié avec succès!')
            navigate('/users')
        } catch (error) {
            toast.error(`Échec de la modification de l'utilisateur: ${error instanceof Error ? error.message : 'Erreur inconnue'}`)
        }
    }

    if (loading) return <Loader />
    if (!user) return <div>User not found</div>

    return (
        <div className="max-w-2xl">
            <h1 className="text-2xl font-bold mb-6">Edit User</h1>
            <Card>
                <UserForm onSubmit={handleSubmit} initialData={user} isEdit />
            </Card>
        </div>
    )
}
