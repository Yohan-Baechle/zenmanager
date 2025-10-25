import { useForm } from 'react-hook-form'
import Input from '../../common/Input'
import Select from '../../common/Select'
import Button from '../../common/Button'
import type { CreateUserDto, UpdateUserDto } from '../../../types/user.types'

interface UserFormProps {
    initialData?: UpdateUserDto
    onSubmit: (data: CreateUserDto | UpdateUserDto) => void | Promise<void>
    isEdit?: boolean
}

export default function UserForm({ initialData, onSubmit, isEdit = false }: UserFormProps) {
    const { register, handleSubmit, formState: { errors } } = useForm<CreateUserDto | UpdateUserDto>({
        defaultValues: initialData,
    })

    const roleOptions = [
        { value: 'employee', label: 'Employé' },
        { value: 'manager', label: 'Manager' },
    ]

    return (
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <Input
                label="Identifiant"
                {...register('username', { required: !isEdit && 'Username is required' })}
                error={errors.username?.message}
            />
            <Input
                label="Prénom"
                {...register('firstName', { required: 'First name is required' })}
                error={errors.firstName?.message}
            />
            <Input
                label="Nom"
                {...register('lastName', { required: 'Last name is required' })}
                error={errors.lastName?.message}
            />
            <Input
                label="Adresse e-mail"
                type="email"
                {...register('email', { required: 'Email is required' })}
                error={errors.email?.message}
            />
            <Input
                label="Numéro de téléphone"
                {...register('phoneNumber', { required: 'Phone number is required' })}
                error={errors.phoneNumber?.message}
            />
            <Select
                label="Rôle"
                options={roleOptions}
                {...register('role', { required: 'Role is required' })}
                error={errors.role?.message}
            />
            <Button type="submit">{isEdit ? 'Modifier l\'utilisateur' : 'Créer l\'utilisateur'}</Button>
        </form>
    )
}
