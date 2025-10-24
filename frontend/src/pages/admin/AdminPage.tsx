import { useState, useEffect } from 'react'
import Card from '../../components/common/Card'
import Button from '../../components/common/Button'
import Modal from '../../components/common/Modal'
import UserList from '../../components/features/users/UserList'
import UserForm from '../../components/features/users/UserForm'
import { usersApi } from '../../api/users.api'
import type { User, CreateUserDto, UpdateUserDto } from '../../types/user.types'

export default function AdminPage() {
    const [users, setUsers] = useState<User[]>([])
    const [loading, setLoading] = useState(true)
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false)
    const [isEditModalOpen, setIsEditModalOpen] = useState(false)
    const [selectedUser, setSelectedUser] = useState<User | null>(null)
    const [error, setError] = useState<string | null>(null)

    useEffect(() => {
        fetchUsers()
    }, [])

    const fetchUsers = async () => {
        try {
            setLoading(true)
            setError(null)
            const data = await usersApi.getAll()
            setUsers(data)
        } catch (err) {
            setError('Failed to load users')
            console.error('Error fetching users:', err)
        } finally {
            setLoading(false)
        }
    }

    const handleCreate = async (data: CreateUserDto | UpdateUserDto) => {
        try {
            await usersApi.create(data as CreateUserDto)
            setIsCreateModalOpen(false)
            fetchUsers()
        } catch (err) {
            console.error('Error creating user:', err)
            alert('Failed to create user')
        }
    }

    const handleEdit = (id: number) => {
        const user = users.find(u => u.id === id)
        if (user) {
            setSelectedUser(user)
            setIsEditModalOpen(true)
        }
    }

    const handleUpdate = async (data: UpdateUserDto) => {
        if (!selectedUser) return

        try {
            await usersApi.update(selectedUser.id, data)
            setIsEditModalOpen(false)
            setSelectedUser(null)
            fetchUsers()
        } catch (err) {
            console.error('Error updating user:', err)
            alert('Failed to update user')
        }
    }

    const handleDelete = async (id: number) => {
        if (!confirm('Are you sure you want to delete this user?')) return

        try {
            await usersApi.delete(id)
            fetchUsers()
        } catch (err) {
            console.error('Error deleting user:', err)
            alert('Failed to delete user')
        }
    }

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold">Administration</h1>
                <Button onClick={() => setIsCreateModalOpen(true)}>
                    Create User
                </Button>
            </div>

            <Card title="User Management">
                {loading ? (
                    <p className="text-gray-600">Loading users...</p>
                ) : error ? (
                    <p className="text-red-600">{error}</p>
                ) : users.length === 0 ? (
                    <p className="text-gray-600">No users found</p>
                ) : (
                    <UserList
                        users={users}
                        onEdit={handleEdit}
                        onDelete={handleDelete}
                    />
                )}
            </Card>

            <Modal
                isOpen={isCreateModalOpen}
                onClose={() => setIsCreateModalOpen(false)}
                title="Create New User"
            >
                <UserForm onSubmit={handleCreate} />
            </Modal>

            <Modal
                isOpen={isEditModalOpen}
                onClose={() => {
                    setIsEditModalOpen(false)
                    setSelectedUser(null)
                }}
                title="Edit User"
            >
                {selectedUser && (
                    <UserForm
                        initialData={{
                            firstName: selectedUser.firstName,
                            lastName: selectedUser.lastName,
                            email: selectedUser.email,
                            phoneNumber: selectedUser.phoneNumber,
                            role: selectedUser.role,
                        }}
                        onSubmit={handleUpdate}
                        isEdit
                    />
                )}
            </Modal>
        </div>
    )
}
