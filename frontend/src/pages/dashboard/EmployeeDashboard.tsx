import { useEffect, useState, useMemo } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../../hooks/useAuth'
import { usersApi } from '../../api/users.api'
import { clocksApi } from '../../api/clocks.api'
import type { Clock } from '../../types/clock.types'
import ClockInOut from '../../components/features/clocks/ClockInOut'
import Card from '../../components/common/Card'
import Table from '../../components/common/Table'
import Loader from '../../components/common/Loader'
import { HistoryIcon } from '../../assets/icons/history'
import { ClockFarsightAnalogIcon } from '../../assets/icons/clock-farsight-analog'
import { CompareArrowsIcon } from '../../assets/icons/compare-arrows'

export default function EmployeeDashboard() {
    const { user, role } = useAuth()
    const [clocks, setClocks] = useState<Clock[]>([])
    const [pendingRequestsCount, setPendingRequestsCount] = useState(0)
    const [loading, setLoading] = useState(true)

    const isManagerOrAdmin = role === 'admin' || role === 'manager'

    const fetchClocks = async () => {
        if (!user) return
        try {
            const data = await usersApi.getClocks(user.id)
            setClocks(data)
        } catch (error) {
            console.error('Failed to load clocks', error)
        }
    }

    const fetchPendingRequests = async () => {
        if (!isManagerOrAdmin) return
        try {
            const requests = await clocksApi.getClocksRequest({ status: 'PENDING' })
            setPendingRequestsCount(Array.isArray(requests) ? requests.length : 0)
        } catch (error) {
            console.error('Failed to load pending requests', error)
        }
    }

    useEffect(() => {
        const loadData = async () => {
            await fetchClocks()
            if (isManagerOrAdmin) {
                await fetchPendingRequests()
            }
            setLoading(false)
        }
        loadData()
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

            {isManagerOrAdmin && pendingRequestsCount > 0 && (
                <Link
                    to="/clock-requests"
                    className="group block bg-[var(--c1)] border border-[var(--c2)] rounded-[20px] p-6 hover:border-[var(--c3)] transition-colors"
                >
                    <div className="flex items-center gap-4">
                        <div className="flex items-center justify-center w-12 h-12 rounded-full bg-[var(--c5)] text-[var(--c1)]">
                            <span className="text-xl font-bold">{pendingRequestsCount}</span>
                        </div>
                        <div className="flex-1">
                            <p className="font-semibold text-[var(--c5)]">
                                Demande{pendingRequestsCount > 1 ? 's' : ''} de badgeage en attente
                            </p>
                            <p className="text-sm text-[var(--c4)]">Cliquez pour traiter</p>
                        </div>
                        <span className="text-[var(--c4)] group-hover:text-[var(--c5)] group-hover:translate-x-1 transition-all text-xl">→</span>
                    </div>
                </Link>
            )}

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
