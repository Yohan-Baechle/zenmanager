import { useEffect, useState, useMemo } from 'react'
import { useAuth } from '../../hooks/useAuth'
import { usersApi } from '../../api/users.api'
import type { Clock } from '../../types/clock.types'
import ClockInOut from '../../components/features/clocks/ClockInOut'
import Card from '../../components/common/Card'
import Table from '../../components/common/Table'
import Loader from '../../components/common/Loader'
import { HistoryIcon } from '../../assets/icons/history'
import { ClockFarsightAnalogIcon } from '../../assets/icons/clock-farsight-analog'
import { CompareArrowsIcon } from '../../assets/icons/compare-arrows'

export default function EmployeeDashboard() {
    const { user } = useAuth()
    const [clocks, setClocks] = useState<Clock[]>([])
    const [loading, setLoading] = useState(true)

    const fetchClocks = async () => {
        if (!user) return
        try {
            const data = await usersApi.getClocks(user.id)
            setClocks(data)
        } catch (error) {
            console.error('Failed to load clocks', error)
        } finally {
            setLoading(false)
        }
    }

    useEffect(() => {
        fetchClocks()
    }, [user])

    const todayClocks = useMemo(() => {
        const today = new Date().toDateString()
        return clocks
            .filter(clock => new Date(clock.time).toDateString() === today)
            .sort((a, b) => new Date(b.time).getTime() - new Date(a.time).getTime())
    }, [clocks])

    const lastClock = todayClocks[0]
    const isWorking = lastClock?.status === true

    const columns = [
        {
            header: 'Heure',
            icon: ClockFarsightAnalogIcon,
            accessor: (clock: Clock) => new Date(clock.time).toLocaleTimeString()
        },
        {
            header: 'Type',
            icon: CompareArrowsIcon,
            accessor: (clock: Clock) => (
                <span className="text-sm font-medium text-[var(--c1)] bg-[var(--c4)] px-2 py-0.5 rounded-full inline-block w-fit">
                    {clock.status ? '↓ Entrée' : '↑ Sortie'}
                </span>
            )
        },
    ]

    if (loading) return <Loader />

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold">
                    Bonjour {user?.firstName} !
                </h1>
                <p className="text-[var(--c4)] mt-1">
                    {isWorking ? 'Vous êtes actuellement au bureau' : 'Vous êtes actuellement absent'}
                </p>
            </div>

            <div className="flex flex-col lg:flex-row gap-4">
                <div className="w-full lg:w-80">
                    <ClockInOut onClockSuccess={fetchClocks} />
                </div>
                <div className="flex-1">
                    <Card title="Pointages du jour" icon={HistoryIcon}>
                        {todayClocks.length > 0 ? (
                            <Table data={todayClocks} columns={columns} />
                        ) : (
                            <p className="text-[var(--c4)] text-center py-4">
                                Aucun pointage aujourd'hui
                            </p>
                        )}
                    </Card>
                </div>
            </div>
        </div>
    )
}
