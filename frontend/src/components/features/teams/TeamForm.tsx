import { useForm } from 'react-hook-form'
import { useState, useEffect } from 'react'
import Input from '../../common/Input'
import Select from '../../common/Select'
import Textarea from '../../common/Textarea'
import Button from '../../common/Button'
import { usersApi } from '../../../api/users.api'
import type { CreateTeamDto, UpdateTeamDto } from '../../../types/team.types'
import type { User } from '../../../types/user.types'

interface TeamFormProps {
    initialData?: UpdateTeamDto & { id?: number }
    onSubmit: (data: CreateTeamDto | UpdateTeamDto) => void | Promise<void>
    isEdit?: boolean
}

export default function TeamForm({ initialData, onSubmit, isEdit = false }: TeamFormProps) {
    const { register, handleSubmit, formState: { errors } } = useForm({
        defaultValues: initialData,
    })

    const [managers, setManagers] = useState<User[]>([])

    useEffect(() => {
        const fetchManagers = async () => {
            try {
                const response = await usersApi.getAll(1, 100)
                setManagers(response.data.filter(user => user.role === 'manager'))
            } catch (err) {
                console.error('Error fetching managers:', err)
            }
        }
        fetchManagers()
    }, [])

    const managerOptions = [
        { value: '', label: 'Sélectionner un manager' },
        ...managers.map(manager => ({
            value: String(manager.id),
            label: `${manager.firstName} ${manager.lastName}`
        }))
    ]

    return (
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <Input
                label="Nom de l'équipe"
                {...register('name', { required: 'Le nom de l\'équipe est requis' })}
                error={errors.name?.message}
            />
            <Textarea
                label="Description"
                rows={3}
                {...register('description', { required: 'La description est requise' })}
                error={errors.description?.message}
            />
            <Select
                label="Manager"
                floatingLabel={true}
                options={managerOptions}
                defaultValue={initialData?.managerId ? String(initialData.managerId) : ''}
                {...register('managerId', {
                    required: 'Le manager est requis',
                    setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
                error={errors.managerId?.message}
            />
            <Button type="submit">{isEdit ? 'Modifier' : 'Créer'} l'équipe</Button>
        </form>
    )
}
