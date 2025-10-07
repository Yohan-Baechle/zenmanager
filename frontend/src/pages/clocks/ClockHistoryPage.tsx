import { useEffect, useState } from 'react'
import { useAuth } from '../../hooks/useAuth'
import { clocksApi } from '../../api/clocks.api'
import type { Clock } from '../../types/clock.types'
import ClockHistory from '../../components/features/clocks/ClockHistory'
import Card from '../../components/common/Card'
import Loader from '../../components/common/Loader'

export default function ClockHistoryPage() {
    const { user } = useAuth()
    const [clocks, setClocks] = useState<Clock[]>([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        loadClocks()
    }, [])

    const loadClocks = async () => {
        if (!user) return
        try {
            const data = await clocksApi.getUserClocks(user.id)
            setClocks(data)
        } catch (error) {
            console.error('Failed to load clocks', error)
        } finally {
            setLoading(false)
        }
    }

    if (loading) return <Loader />

    return (
        <div className="space-y-4">
            <h1 className="text-2xl font-bold">Clock History</h1>
            <Card>
                <ClockHistory clocks={clocks} />
            </Card>
        </div>
    )
}
