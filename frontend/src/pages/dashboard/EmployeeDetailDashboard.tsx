import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { usersApi } from '../../api/users.api'
import { clocksApi } from '../../api/clocks.api'
import type { User } from '../../types/user.types'
import type { WorkingHoursSummary } from '../../types/clock.types'
import Card from '../../components/common/Card'
import KPICard from '../../components/features/reports/KPICard'
import Loader from '../../components/common/Loader'

export default function EmployeeDetailDashboard() {
    const { id } = useParams<{ id: string }>()
    const [user, setUser] = useState<User | null>(null)
    const [summary, setSummary] = useState<WorkingHoursSummary | null>(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        loadData()
    }, [id])

    const loadData = async () => {
        if (!id) return
        try {
            const userData = await usersApi.getById(Number(id))
            const summaryData = await clocksApi.getWorkingSummary(Number(id))
            setUser(userData)
            setSummary(summaryData)
        } catch (error) {
            console.error('Failed to load employee data', error)
        } finally {
            setLoading(false)
        }
    }

    if (loading) return <Loader />
    if (!user) return <div>Employee not found</div>

    return (
        <div className="space-y-6">
            <h1 className="text-2xl font-bold">
                {user.firstName} {user.lastName} - Details
            </h1>

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

            <Card title="Employee Information">
                <div className="space-y-2">
                    <p><strong>Email:</strong> {user.email}</p>
                    <p><strong>Phone:</strong> {user.phoneNumber}</p>
                    <p><strong>Role:</strong> {user.role}</p>
                </div>
            </Card>
        </div>
    )
}
