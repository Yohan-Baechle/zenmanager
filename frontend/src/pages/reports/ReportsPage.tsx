import { useState } from 'react'
import ReportFilters from '../../components/features/reports/ReportFilters'
import ReportChart from '../../components/features/reports/ReportChart'

interface ReportFilters {
    startDate?: string
    endDate?: string
    userId?: string
    projectId?: string
}

export default function ReportsPage() {
    const [filters, setFilters] = useState<ReportFilters | null>(null)

    const handleApplyFilters = (newFilters: ReportFilters) => {
        setFilters(newFilters)
        console.log('Filters applied:', newFilters)
    }

    return (
        <div className="space-y-6">
            <h1 className="text-2xl font-bold">Reports</h1>
            <ReportFilters onApply={handleApplyFilters} />

            {filters && (
                <div className="text-sm text-gray-600 bg-gray-50 p-3 rounded">
                    Active filters: {JSON.stringify(filters, null, 2)}
                </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <ReportChart title="Working Hours" data={[]} />
                <ReportChart title="Team Performance" data={[]} />
            </div>
        </div>
    )
}
