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
        { value: 'employee', label: 'Employee' },
        { value: 'manager', label: 'Manager' },
        { value: 'admin', label: 'Admin' },
    ]

    const handleFormSubmit = (data: CreateUserDto | UpdateUserDto) => {
        onSubmit(data)
    }

    return (
        <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-4">
            <Input
                label="First Name"
                {...register('firstName', { required: 'First name is required' })}
                error={errors.firstName?.message}
            />
            <Input
                label="Last Name"
                {...register('lastName', { required: 'Last name is required' })}
                error={errors.lastName?.message}
            />
            <Input
                label="Email"
                type="email"
                {...register('email', { required: 'Email is required' })}
                error={errors.email?.message}
            />
            <Input
                label="Phone Number"
                {...register('phoneNumber', { required: 'Phone number is required' })}
                error={errors.phoneNumber?.message}
            />
            {!isEdit && (
                <Input
                    label="Password"
                    type="password"
                    visible
                    {...register('password', { required: 'Password is required' })}
                    error={errors.password?.message}
                />
            )}
            <Select
                label="Role"
                options={roleOptions}
                {...register('role', { required: 'Role is required' })}
                error={errors.role?.message}
            />
            <Button type="submit">{isEdit ? 'Update User' : 'Create User'}</Button>
        </form>
    )
}
