import { useForm } from 'react-hook-form'
import Input from '../../common/Input'
import Button from '../../common/Button'
import type { CreateUserDto, UpdateUserDto } from '../../../types/user.types'

interface UserFormProps {
    initialData?: UpdateUserDto
    onSubmit: (data: CreateUserDto | UpdateUserDto) => void | Promise<void>
    isEdit?: boolean
}

export default function UserForm({ initialData, onSubmit, isEdit = false }: UserFormProps) {
    const { register, handleSubmit, formState: { errors } } = useForm({
        defaultValues: initialData,
    })

    return (
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
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
                    {...register('password', { required: 'Password is required' })}
                    error={errors.password?.message}
                />
            )}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select
                    {...register('role', { required: 'Role is required' })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="employee">Employee</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
            <Button type="submit">{isEdit ? 'Update' : 'Create'} User</Button>
        </form>
    )
}
