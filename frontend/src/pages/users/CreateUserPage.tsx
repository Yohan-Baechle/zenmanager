import { useNavigate } from 'react-router-dom'
import { toast } from 'sonner'
import { usersApi } from '../../api/users.api'
import type { CreateUserDto, UpdateUserDto } from '../../types/user.types'
import UserForm from '../../components/features/users/UserForm'
import Card from '../../components/common/Card'

export default function CreateUserPage() {
    const navigate = useNavigate()

    const handleSubmit = async (data: CreateUserDto | UpdateUserDto) => {
        try {
            await usersApi.create(data as CreateUserDto)
            toast.success('Utilisateur créé avec succès!')
            navigate('/users')
        } catch (error) {
            toast.error(`Échec de la création de l'utilisateur: ${error instanceof Error ? error.message : 'Erreur inconnue'}`)
        }
    }

    return (
        <div className="max-w-2xl">
            <h1 className="text-2xl font-bold mb-6">Create User</h1>
            <Card>
                <UserForm onSubmit={handleSubmit} />
            </Card>
        </div>
    )
}
