import { useState, useEffect } from 'react'
import Card from '../../components/common/Card'
import Button from '../../components/common/Button'
import Modal from '../../components/common/Modal'
import UserList from '../../components/features/users/UserList'
import UserForm from '../../components/features/users/UserForm'
import TeamList from '../../components/features/teams/TeamList'
import TeamForm from '../../components/features/teams/TeamForm'
import { usersApi } from '../../api/users.api'
import { teamsApi } from '../../api/teams.api'
import type { User, CreateUserDto, UpdateUserDto } from '../../types/user.types'
import type { Team, CreateTeamDto, UpdateTeamDto } from '../../types/team.types'
import { ArrowBackIosNewIcon } from "../../assets/icons/arrow-back-ios-new.tsx"

export default function AdminPage() {
    const [users, setUsers] = useState<User[]>([])
    const [loading, setLoading] = useState(true)
    const [currentPage, setCurrentPage] = useState(1)
    const [totalPages, setTotalPages] = useState(1)
    const [itemsPerPage] = useState(10)
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false)
    const [isEditModalOpen, setIsEditModalOpen] = useState(false)
    const [selectedUser, setSelectedUser] = useState<User | null>(null)
    const [error, setError] = useState<string | null>(null)

    const [teams, setTeams] = useState<Team[]>([])
    const [teamsLoading, setTeamsLoading] = useState(true)
    const [teamsCurrentPage, setTeamsCurrentPage] = useState(1)
    const [teamsTotalPages, setTeamsTotalPages] = useState(1)
    const [teamsPerPage] = useState(10)
    const [isCreateTeamModalOpen, setIsCreateTeamModalOpen] = useState(false)
    const [isEditTeamModalOpen, setIsEditTeamModalOpen] = useState(false)
    const [selectedTeam, setSelectedTeam] = useState<Team | null>(null)
    const [teamsError, setTeamsError] = useState<string | null>(null)

    useEffect(() => {
        fetchUsers()
        fetchTeams()
    }, [])

    const fetchUsers = async (page: number = currentPage) => {
        try {
            setLoading(true)
            setError(null)
            const response = await usersApi.getAll(page, itemsPerPage)
            setUsers(response.data)
            setTotalPages(response.meta.totalPages)
            setCurrentPage(page)
        } catch (err) {
            setError('Failed to load users')
            console.error('Error fetching users:', err)
        } finally {
            setLoading(false)
        }
    }

    const handlePrevious = () => {
        if (currentPage > 1) fetchUsers(currentPage - 1)
    }

    const handleNext = () => {
        if (currentPage < totalPages) fetchUsers(currentPage + 1)
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

    const fetchTeams = async (page: number = teamsCurrentPage) => {
        try {
            setTeamsLoading(true)
            setTeamsError(null)
            const response = await teamsApi.getAll(page, teamsPerPage)
            setTeams(response.data)
            setTeamsTotalPages(response.meta.totalPages)
            setTeamsCurrentPage(page)
        } catch (err) {
            setTeamsError('Échec du chargement des équipes')
            console.error('Error fetching teams:', err)
        } finally {
            setTeamsLoading(false)
        }
    }

    const handleTeamsPrevious = () => {
        if (teamsCurrentPage > 1) fetchTeams(teamsCurrentPage - 1)
    }

    const handleTeamsNext = () => {
        if (teamsCurrentPage < teamsTotalPages) fetchTeams(teamsCurrentPage + 1)
    }

    const handleCreateTeam = async (data: CreateTeamDto | UpdateTeamDto) => {
        try {
            await teamsApi.create(data as CreateTeamDto)
            setIsCreateTeamModalOpen(false)
            fetchTeams()
        } catch (err) {
            console.error('Error creating team:', err)
            alert('Échec de la création de l\'équipe')
        }
    }

    const handleEditTeam = (id: number) => {
        const team = teams.find(t => t.id === id)
        if (team) {
            setSelectedTeam(team)
            setIsEditTeamModalOpen(true)
        }
    }

    const handleUpdateTeam = async (data: UpdateTeamDto) => {
        if (!selectedTeam) return

        try {
            await teamsApi.update(selectedTeam.id, data)
            setIsEditTeamModalOpen(false)
            setSelectedTeam(null)
            fetchTeams()
        } catch (err) {
            console.error('Error updating team:', err)
            alert('Échec de la modification de l\'équipe')
        }
    }

    const handleDeleteTeam = async (id: number) => {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette équipe ?')) return

        try {
            await teamsApi.delete(id)
            fetchTeams()
        } catch (err) {
            console.error('Error deleting team:', err)
            alert('Échec de la suppression de l\'équipe')
        }
    }

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold">Administration</h1>
            </div>

            <Card title="Gestion des utilisateurs">
                <div className="flex items-center text-sm text-[var(--c5)]">
                    <button
                        onClick={handlePrevious}
                        disabled={currentPage === 1}
                        className="p-2 rounded-s-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 cursor-pointer"
                    ><ArrowBackIosNewIcon className="h-5 w-5"/></button>
                    <div className="font-medium h-9 p-2 flex items-center bg-[var(--c2)]/50 border-l border-r border-[var(--c2)]">
                        {currentPage}/{totalPages} Page{totalPages > 1 ? 's' : ''}
                    </div>
                    <button
                        onClick={handleNext}
                        disabled={currentPage === totalPages || totalPages === 0}
                        className="p-2 rounded-e-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 cursor-pointer"
                    ><ArrowBackIosNewIcon className="h-5 w-5 rotate-180"/></button>
                </div>
                {loading ? (
                    <p className="text-gray-600">Loading users...</p>
                ) : error ? (
                    <p className="text-red-600">{error}</p>
                ) : users.length === 0 ? (
                    <p className="text-gray-600">No users found</p>
                ) : (
                    <UserList users={users} onEdit={handleEdit} onDelete={handleDelete} />
                )}
                <div className="mt-4 flex justify-end">
                    <Button onClick={() => setIsCreateModalOpen(true)}>
                        Créer un nouvel utilisateur
                    </Button>
                </div>
            </Card>

            <Card title="Gestion des équipes">
                <div className="flex items-center text-sm text-[var(--c5)]">
                    <button
                        onClick={handleTeamsPrevious}
                        disabled={teamsCurrentPage === 1}
                        className="p-2 rounded-s-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 cursor-pointer"
                    ><ArrowBackIosNewIcon className="h-5 w-5"/></button>
                    <div className="font-medium h-9 p-2 flex items-center bg-[var(--c2)]/50 border-l border-r border-[var(--c2)]">
                        {teamsCurrentPage}/{teamsTotalPages} Page{teamsTotalPages > 1 ? 's' : ''}
                    </div>
                    <button
                        onClick={handleTeamsNext}
                        disabled={teamsCurrentPage === teamsTotalPages || teamsTotalPages === 0}
                        className="p-2 rounded-e-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 cursor-pointer"
                    ><ArrowBackIosNewIcon className="h-5 w-5 rotate-180"/></button>
                </div>
                {teamsLoading ? (
                    <p className="text-gray-600">Chargement des équipes...</p>
                ) : teamsError ? (
                    <p className="text-red-600">{teamsError}</p>
                ) : teams.length === 0 ? (
                    <p className="text-gray-600">Aucune équipe trouvée</p>
                ) : (
                    <TeamList teams={teams} onEdit={handleEditTeam} onDelete={handleDeleteTeam} />
                )}
                <div className="mt-4 flex justify-end">
                    <Button onClick={() => setIsCreateTeamModalOpen(true)}>
                        Créer une nouvelle équipe
                    </Button>
                </div>
            </Card>

            <Modal
                isOpen={isCreateModalOpen}
                onClose={() => setIsCreateModalOpen(false)}
                title="Créer un nouvel utilisateur"
            >
                <UserForm onSubmit={handleCreate}/>
            </Modal>

            <Modal
                isOpen={isEditModalOpen}
                onClose={() => {
                    setIsEditModalOpen(false)
                    setSelectedUser(null)
                }}
                title="Modifier un utilisateur"
            >
                {selectedUser && (
                    <UserForm
                        initialData={{
                            username: selectedUser.username,
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

            <Modal
                isOpen={isCreateTeamModalOpen}
                onClose={() => setIsCreateTeamModalOpen(false)}
                title="Créer une nouvelle équipe"
            >
                <TeamForm onSubmit={handleCreateTeam}/>
            </Modal>

            <Modal
                isOpen={isEditTeamModalOpen}
                onClose={() => {
                    setIsEditTeamModalOpen(false)
                    setSelectedTeam(null)
                }}
                title="Modifier une équipe"
            >
                {selectedTeam && (
                    <TeamForm
                        initialData={{
                            name: selectedTeam.name,
                            description: selectedTeam.description,
                            managerId: selectedTeam.manager?.id,
                        }}
                        onSubmit={handleUpdateTeam}
                        isEdit
                    />
                )}
            </Modal>
        </div>
    )
}
