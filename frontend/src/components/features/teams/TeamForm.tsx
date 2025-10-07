import { useForm } from 'react-hook-form'
import Input from '../../common/Input'
import Button from '../../common/Button'
import type { CreateTeamDto, UpdateTeamDto } from '../../../types/team.types'

interface TeamFormProps {
    initialData?: UpdateTeamDto
    onSubmit: (data: CreateTeamDto | UpdateTeamDto) => void | Promise<void>
    isEdit?: boolean
}

export default function TeamForm({ initialData, onSubmit, isEdit = false }: TeamFormProps) {
    const { register, handleSubmit, formState: { errors } } = useForm({
        defaultValues: initialData,
    })

    return (
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <Input
                label="Team Name"
                {...register('name', { required: 'Team name is required' })}
                error={errors.name?.message}
            />
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                    {...register('description', { required: 'Description is required' })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    rows={3}
                />
                {errors.description && (
                    <p className="mt-1 text-sm text-red-600">{errors.description.message}</p>
                )}
            </div>
            <Input
                label="Manager ID"
                type="number"
                {...register('managerId', { required: 'Manager ID is required', valueAsNumber: true })}
                error={errors.managerId?.message}
            />
            <Button type="submit">{isEdit ? 'Update' : 'Create'} Team</Button>
        </form>
    )
}
