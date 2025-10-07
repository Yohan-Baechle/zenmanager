import { useEffect, useState } from 'react'
import { useAuth } from '../../hooks/useAuth'
import { clocksApi } from '../../api/clocks.api'
import type { WorkingHoursSummary } from '../../types/clock.types'
import KPICard from '../../components/features/reports/KPICard'
import Card from '../../components/common/Card'
import Loader from '../../components/common/Loader'

export default function EmployeeDashboard() {
    const { user } = useAuth()
    const [summary, setSummary] = useState<WorkingHoursSummary | null>(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        loadSummary()
    }, [])

    const loadSummary = async () => {
        if (!user) return
        try {
            const data = await clocksApi.getWorkingSummary(user.id)
            setSummary(data)
        } catch (error) {
            console.error('Failed to load summary', error)
        } finally {
            setLoading(false)
        }
    }

    if (loading) return <Loader />

    return (
        <div className="space-y-6">
            <h1 className="text-2xl font-bold">My Dashboard</h1>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <KPICard
                    label="Total Hours"
                    value={summary?.totalHours.toFixed(2) || '0'}
                    icon="⏱️"
                    color="blue"
                />
                <KPICard
                    label="Daily Average"
                    value={summary?.dailyAverage.toFixed(2) || '0'}
                    icon="📅"
                    color="green"
                />
                <KPICard
                    label="Weekly Average"
                    value={summary?.weeklyAverage.toFixed(2) || '0'}
                    icon="📊"
                    color="purple"
                />
            </div>

            <Card title="Recent Activity">
                <p className="text-gray-600">
                    Your working hours summary and recent clock entries
                </p>
            </Card>
        </div>
    )
}
