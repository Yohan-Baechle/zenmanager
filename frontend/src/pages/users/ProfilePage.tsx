import { useState } from 'react'
import { useAuth } from '../../hooks/useAuth'
import { usersApi } from '../../api/users.api'
import type { UpdateUserDto, CreateUserDto } from '../../types/user.types'
import UserForm from '../../components/features/users/UserForm'
import Card from '../../components/common/Card'

export default function ProfilePage() {
    const { user } = useAuth()
    const [success, setSuccess] = useState(false)

    const handleSubmit = async (data: CreateUserDto | UpdateUserDto) => {
        try {
            await usersApi.update(user!.id, data as UpdateUserDto)
            setSuccess(true)
            setTimeout(() => setSuccess(false), 3000)
        } catch (error) {
            alert(`Failed to update profile: ${error instanceof Error ? error.message : 'Unknown error'}`)
        }
    }

    if (!user) return null

    return (
        <div className="max-w-2xl">
            <h1 className="text-2xl font-bold mb-6">My Profile</h1>
            {success && (
                <div className="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                    Profile updated successfully!
                </div>
            )}
            <Card>
                <UserForm onSubmit={handleSubmit} initialData={user} isEdit />
            </Card>
        </div>
    )
}
