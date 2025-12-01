import { useState, useEffect } from 'react'
import Modal from '../../common/Modal'
import { usersApi } from '../../../api/users.api'
import type { User } from '../../../types/user.types'
import Loader from '../../common/Loader'
import { SearchIcon } from '../../../assets/icons/search'

interface UserSearchModalProps {
    isOpen: boolean
    onClose: () => void
    teamId: number
    onUserUpdated: () => void
}

export default function UserSearchModal({ isOpen, onClose, teamId, onUserUpdated }: UserSearchModalProps) {
    const [users, setUsers] = useState<User[]>([])
    const [loading, setLoading] = useState(false)
    const [searchTerm, setSearchTerm] = useState('')
    const [processingId, setProcessingId] = useState<number | null>(null)
    const [error, setError] = useState<string | null>(null)

    useEffect(() => {
        if (isOpen) {
            loadUsers()
        }
    }, [isOpen])

    const loadUsers = async () => {
        setLoading(true)
        try {
            const response = await usersApi.getAll(1, 1000)
            setUsers(response.data)
        } catch (err) {
            console.error('Failed to load users', err)
            setError('Impossible de charger les utilisateurs')
        } finally {
            setLoading(false)
        }
    }

    const handleAddUser = async (user: User) => {
        setProcessingId(user.id)
        setError(null)
        try {
            await usersApi.update(user.id, { teamId })
            await loadUsers()
            onUserUpdated()
        } catch (err) {
            console.error('Failed to add user to team', err)
            setError('Impossible d\'ajouter l\'utilisateur à l\'équipe')
        } finally {
            setProcessingId(null)
        }
    }

    const handleRemoveUser = async (user: User) => {
        setProcessingId(user.id)
        setError(null)
        try {
            await usersApi.removeFromTeam(user.id)
            await loadUsers()
            onUserUpdated()
        } catch (err) {
            console.error('Failed to remove user from team', err)
            setError('Impossible de retirer l\'utilisateur de l\'équipe')
        } finally {
            setProcessingId(null)
        }
    }

    const filteredUsers = users.filter(user => {
        if (user.role === 'admin' || user.role === 'manager') return false

        const searchLower = searchTerm.toLowerCase()
        const matchesSearch =
            user.username.toLowerCase().includes(searchLower) ||
            user.email.toLowerCase().includes(searchLower) ||
            user.firstName.toLowerCase().includes(searchLower) ||
            user.lastName.toLowerCase().includes(searchLower)

        return matchesSearch
    })

    if (!isOpen) return null

    return (
        <Modal isOpen={isOpen} onClose={onClose} title="Gérer les membres de l'équipe">
            <div className="space-y-4">
                {error && (
                    <div className="p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                        {error}
                    </div>
                )}

                <div className="relative">
                    <input
                        type="text"
                        placeholder="Rechercher un utilisateur..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="w-full pl-10 pr-4 py-2 rounded-xl border border-[var(--c2)] bg-[var(--c1)] focus:outline-none focus:border-[var(--c4)]"
                    />
                    <SearchIcon className="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                </div>

                <div className="max-h-96 overflow-y-auto space-y-2">
                    {loading ? (
                        <Loader />
                    ) : filteredUsers.length === 0 ? (
                        <p className="text-center text-gray-500 py-4">Aucun utilisateur trouvé</p>
                    ) : (
                        filteredUsers.map(user => {
                            const isInCurrentTeam = user.team?.id === teamId
                            const hasOtherTeam = user.team && user.team.id !== teamId

                            return (
                                <div key={user.id} className="flex items-center justify-between p-3 border border-[var(--c2)] rounded-lg bg-[var(--c1)]">
                                    <div>
                                        <p className="font-medium">{user.firstName} {user.lastName}</p>
                                        <p className="text-sm text-gray-500">{user.email}</p>
                                        {hasOtherTeam && (
                                            <p className="text-xs text-orange-500">Déjà dans une autre équipe</p>
                                        )}
                                    </div>

                                    {isInCurrentTeam ? (
                                        <button
                                            onClick={() => handleRemoveUser(user)}
                                            disabled={!!processingId}
                                            className="px-3 py-1 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 disabled:opacity-50"
                                        >
                                            {processingId === user.id ? '...' : 'Retirer'}
                                        </button>
                                    ) : !hasOtherTeam ? (
                                        <button
                                            onClick={() => handleAddUser(user)}
                                            disabled={!!processingId}
                                            className="px-3 py-1 text-sm bg-[var(--c2)] text-[var(--c5)] rounded-lg hover:opacity-90 disabled:opacity-50"
                                        >
                                            {processingId === user.id ? '...' : 'Ajouter'}
                                        </button>
                                    ) : (
                                        <span className="text-xs text-gray-400">Indisponible</span>
                                    )}
                                </div>
                            )
                        })
                    )}
                </div>
            </div>
        </Modal>
    )
}
