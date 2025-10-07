import Input from '../../common/Input'
import Button from '../../common/Button'

interface ReportFiltersProps {
    onApply: (filters: any) => void
}

export default function ReportFilters({ onApply }: ReportFiltersProps) {
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault()
        const formData = new FormData(e.target as HTMLFormElement)
        onApply({
            startDate: formData.get('startDate'),
            endDate: formData.get('endDate'),
            teamId: formData.get('teamId'),
        })
    }

    return (
        <form onSubmit={handleSubmit} className="flex gap-4 items-end">
            <Input label="Start Date" type="date" name="startDate" />
            <Input label="End Date" type="date" name="endDate" />
            <Input label="Team ID" type="number" name="teamId" placeholder="Optional" />
            <Button type="submit">Apply Filters</Button>
        </form>
    )
}
