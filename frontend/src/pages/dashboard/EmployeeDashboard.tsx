import { useEffect, useState, useMemo } from 'react'
import { useAuth } from '../../hooks/useAuth'
import { usersApi } from '../../api/users.api'
import { reportsApi } from '../../api/reports.api'
import type { Clock } from '../../types/clock.types'
import type { KPIs } from '../../types/kpi.types'
import ClockInOut from '../../components/features/clocks/ClockInOut'
import Card from '../../components/common/Card'
import Table from '../../components/common/Table'
import KPICard from '../../components/features/reports/KPICard'
import Loader from '../../components/common/Loader'
import { HistoryIcon } from '../../assets/icons/history'
import { ClockFarsightAnalogIcon } from '../../assets/icons/clock-farsight-analog'
import { CompareArrowsIcon } from '../../assets/icons/compare-arrows'

export default function EmployeeDashboard() {
    const { user, role } = useAuth()
    const [clocks, setClocks] = useState<Clock[]>([])
    const [kpis, setKpis] = useState<KPIs | null>(null)
    const [loading, setLoading] = useState(true)

    const fetchClocks = async () => {
        if (!user) return
        try {
            const data = await usersApi.getClocks(user.id)
            setClocks(data)
        } catch (error) {
            console.error('Failed to load clocks', error)
        }
    }

    const fetchKpis = async () => {
        if (!user) return
        try {
            const now = new Date()
            const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
            const response = await reportsApi.getReports({
                start_date: startOfMonth.toISOString().split('T')[0],
                end_date: now.toISOString().split('T')[0],
                user_id: (role === 'admin' || role === 'manager') ? undefined : user.id
            })
            if (response.success) {
                setKpis(response.data.kpis)
            }
        } catch (error) {
            console.error('Failed to load KPIs', error)
        }
    }

    useEffect(() => {
        const loadData = async () => {
            await Promise.all([fetchClocks(), fetchKpis()])
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

    const isManagerOrAdmin = role === 'admin' || role === 'manager'

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

            {kpis && (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {isManagerOrAdmin ? (
                        <>
                            <KPICard
                                title="Heures travaillées (équipe)"
                                value={kpis.total_working_hours?.toFixed(1) || '0'}
                                unit="heures"
                                description="Total des heures ce mois"
                            />
                            <KPICard
                                title="Taux de retards"
                                value={kpis.late_arrivals_rate?.toFixed(1) || '0'}
                                unit="%"
                                description={`${kpis.late_arrivals_count || 0} retards ce mois`}
                            />
                            <KPICard
                                title="Jours incomplets"
                                value={kpis.incomplete_days_count || 0}
                                unit="jours"
                                description="Pointages manquants"
                            />
                        </>
                    ) : (
                        <>
                            <KPICard
                                title="Mes heures travaillées"
                                value={kpis.total_working_hours?.toFixed(1) || '0'}
                                unit="heures"
                                description="Total ce mois"
                            />
                            <KPICard
                                title="Jours présents"
                                value={kpis.present_days_count || 0}
                                unit="jours"
                                description="Ce mois"
                            />
                            <KPICard
                                title="Mes retards"
                                value={kpis.late_arrivals_count || 0}
                                unit="fois"
                                description="Ce mois"
                            />
                        </>
                    )}
                </div>
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
