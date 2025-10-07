import { useNavigate } from 'react-router-dom'
import { usersApi } from '../../api/users.api'
import type { CreateUserDto, UpdateUserDto } from '../../types/user.types'
import UserForm from '../../components/features/users/UserForm'
import Card from '../../components/common/Card'

export default function CreateUserPage() {
    const navigate = useNavigate()

    const handleSubmit = async (data: CreateUserDto | UpdateUserDto) => {
        try {
            await usersApi.create(data as CreateUserDto)
            navigate('/users')
        } catch (error) {
            alert(`Failed to create user: ${error instanceof Error ? error.message : 'Unknown error'}`)
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
