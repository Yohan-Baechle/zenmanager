import { useState } from 'react'
import { reportsApi } from '../../api/reports.api'
import type { ReportsFilters, ReportsData } from '../../types/kpi.types'
import ReportFilters from '../../components/features/reports/ReportFilters'
import KPICard from '../../components/features/reports/KPICard'
import Card from '../../components/common/Card'
import Loader from '../../components/common/Loader'
import StatCard from '../../components/features/reports/StatCard'
import InfoItem from '../../components/features/reports/InfoItem'

export default function ReportsPage() {
    const [loading, setLoading] = useState(false)
    const [error, setError] = useState<string | null>(null)
    const [reportsData, setReportsData] = useState<ReportsData | null>(null)

    const fetchReports = async (filters: ReportsFilters) => {
        setLoading(true)
        setError(null)

        try {
            const response = await reportsApi.getReports(filters)

            if (response.success) {
                setReportsData(response.data)
            } else {
                setError(response.error || 'Erreur lors du chargement des rapports')
            }
        } catch (err: unknown) {
            console.error('Error fetching reports:', err)
            const error = err as { response?: { data?: { error?: string } }; message?: string }
            setError(
                error.response?.data?.error ||
                error.message ||
                'Erreur lors du chargement des rapports'
            )
        } finally {
            setLoading(false)
        }
    }

    const handleApplyFilters = (filters: ReportsFilters) => {
        fetchReports(filters)
    }

    return (
        <div className="space-y-6">
            <h1 className="text-3xl font-bold">Rapports et Statistiques</h1>

            {/* Filtres */}
            <ReportFilters onApply={handleApplyFilters} loading={loading} />

            {/* Affichage des erreurs */}
            {error && (
                <Card className="bg-red-50 border-red-300">
                    <div className="flex items-start gap-3">
                        <span className="text-2xl">❌</span>
                        <div>
                            <p className="font-bold text-red-800">Erreur</p>
                            <p className="text-red-700 mt-1">{error}</p>
                        </div>
                    </div>
                </Card>
            )}

            {/* Loading state */}
            {loading && !reportsData && (
                <Card className="min-h-[400px] flex items-center justify-center">
                    <div className="text-center">
                        <Loader />
                        <p className="text-xl mt-4 text-[var(--c4)]">Chargement des rapports...</p>
                    </div>
                </Card>
            )}

            {/* Données du rapport */}
            {reportsData && !loading && (
                <>
                    {/* Informations sur la période */}
                    {reportsData.period && (
                        <Card title="📅 Période analysée">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <StatCard
                                    label="Total de jours"
                                    value={reportsData.period.total_days ?? 'N/A'}
                                />
                                <StatCard
                                    label="Jours ouvrables"
                                    value={reportsData.period.working_days ?? 'N/A'}
                                />
                                <StatCard
                                    label="Week-ends"
                                    value={reportsData.period.weekend_days ?? 'N/A'}
                                />
                            </div>
                        </Card>
                    )}

                    {/* Horaires de travail */}
                    {reportsData.work_schedule && (
                        <Card title="🕐 Horaires de travail">
                            <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <InfoItem
                                    label="Début"
                                    value={reportsData.work_schedule.start_time}
                                />
                                <InfoItem
                                    label="Fin"
                                    value={reportsData.work_schedule.end_time}
                                />
                                <InfoItem
                                    label="Tolérance retard"
                                    value={`${reportsData.work_schedule.tolerance_late} min`}
                                />
                                <InfoItem
                                    label="Tolérance départ"
                                    value={`${reportsData.work_schedule.tolerance_early_departure} min`}
                                />
                                <InfoItem
                                    label="Heures/jour"
                                    value={`${reportsData.work_schedule.standard_hours_per_day}h`}
                                />
                            </div>
                        </Card>
                    )}

                    {/* KPIs */}
                    {reportsData.kpis && (
                        <Card title="📊 Indicateurs de performance (KPIs)">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <KPICard
                                    title="Heures travaillées"
                                    value={reportsData.kpis.total_working_hours?.toFixed(1) || '0'}
                                    unit="heures"
                                    description="Nombre total d'heures effectuées par l'employé ou l'équipe durant la période sélectionnée"
                                />
                                <KPICard
                                    title="Jours présents"
                                    value={reportsData.kpis.present_days_count || 0}
                                    unit="jours"
                                    description="Nombre de jours où l'employé a pointé et travaillé normalement"
                                />
                                <KPICard
                                    title="Jours absents"
                                    value={reportsData.kpis.absent_days_count || 0}
                                    unit="jours"
                                    description="Nombre de jours ouvrables où l'employé n'a pas pointé et était absent"
                                />
                                <KPICard
                                    title="Taux de retards"
                                    value={reportsData.kpis.late_arrivals_rate?.toFixed(1) || '0'}
                                    unit="%"
                                    description={`Pourcentage de jours avec retard (${reportsData.kpis.late_arrivals_count || 0} retards sur ${reportsData.kpis.present_days_count || 0} jours présents)`}
                                />
                                <KPICard
                                    title="Départs anticipés"
                                    value={reportsData.kpis.early_departures_count || 0}
                                    unit="fois"
                                    description="Nombre de fois où l'employé est parti avant l'heure de fin - tolérance (30 min)"
                                />
                                <KPICard
                                    title="Jours incomplets"
                                    value={reportsData.kpis.incomplete_days_count || 0}
                                    unit="jours"
                                    description="Jours où l'employé a oublié de pointer à l'entrée ou à la sortie"
                                />
                                <KPICard
                                    title="Total sorties"
                                    value={reportsData.kpis.total_exits_count || 0}
                                    unit="sorties"
                                    description="Nombre total de pointages de sortie effectués durant la période"
                                />
                            </div>
                        </Card>
                    )}
                </>
            )}
        </div>
    )
}
