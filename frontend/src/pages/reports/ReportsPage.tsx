import { useState, useEffect } from 'react'
import { reportsApi } from '../../api/reports.api'
import type { ReportsFilters, ReportsData } from '../../types/kpi.types'
import ReportFilters from '../../components/features/reports/ReportFilters'
import KPICard from '../../components/features/reports/KPICard'

export default function ReportsPage() {
    const [loading, setLoading] = useState(false)
    const [error, setError] = useState<string | null>(null)
    const [reportsData, setReportsData] = useState<ReportsData | null>(null)

    useEffect(() => {
        
    }, [])

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
        } catch (err: any) {
            console.error('Error fetching reports:', err)
            setError(
                err.response?.data?.error || 
                err.message || 
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
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold text-gray-900">Rapports et Statistiques</h1>
            </div>

            {/* Filtres */}
            <ReportFilters onApply={handleApplyFilters} loading={loading} />

           
            {/* Affichage des erreurs */}
            {error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <p className="font-bold">Erreur</p>
                    <p>{error}</p>
                </div>
            )}

            {/* Loading state */}
            {loading && !reportsData && (
                <div className="flex justify-center items-center min-h-[400px]">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                        <div className="text-xl text-gray-600">Chargement des rapports...</div>
                    </div>
                </div>
            )}

            {/* Données du rapport */}
            {reportsData && !loading && (
                <>
                    {/* Informations sur la période */}
                    {reportsData.period && (
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-xl font-semibold mb-4 text-gray-900">
                                Période analysée
                            </h2>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <p className="text-sm text-gray-600 mb-1">Total de jours</p>
                                    <p className="text-3xl font-bold text-blue-600">
                                        {reportsData.period.total_days ?? 'N/A'}
                                    </p>
                                </div>
                                <div className="bg-green-50 p-4 rounded-lg border border-green-200">
                                    <p className="text-sm text-gray-600 mb-1">Jours ouvrables</p>
                                    <p className="text-3xl font-bold text-green-600">
                                        {reportsData.period.working_days ?? 'N/A'}
                                    </p>
                                </div>
                                <div className="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                    <p className="text-sm text-gray-600 mb-1">Week-ends</p>
                                    <p className="text-3xl font-bold text-purple-600">
                                        {reportsData.period.weekend_days ?? 'N/A'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Horaires de travail */}
                    {reportsData.work_schedule && (
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-xl font-semibold mb-4 text-gray-900">
                                Horaires de travail
                            </h2>
                            <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600 mb-1">Début</p>
                                    <p className="text-lg font-semibold text-gray-900">
                                        {reportsData.work_schedule.start_time}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 mb-1">Fin</p>
                                    <p className="text-lg font-semibold text-gray-900">
                                        {reportsData.work_schedule.end_time}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 mb-1">Tolérance retard</p>
                                    <p className="text-lg font-semibold text-gray-900">
                                        {reportsData.work_schedule.tolerance_late} min
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 mb-1">Tolérance départ</p>
                                    <p className="text-lg font-semibold text-gray-900">
                                        {reportsData.work_schedule.tolerance_early_departure} min
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 mb-1">Heures/jour</p>
                                    <p className="text-lg font-semibold text-gray-900">
                                        {reportsData.work_schedule.standard_hours_per_day}h
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* KPIs */}
                    {reportsData.kpis && (
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-xl font-semibold mb-6 text-gray-900">
                                Indicateurs de performance (KPIs)
                            </h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <KPICard
                                    title="Heures travaillées"
                                    value={reportsData.kpis.total_working_hours?.toFixed(1) || '0'}
                                    unit="heures"
                                    color="blue"
                                    icon="⏱️"
                                    description="Nombre total d'heures effectuées par l'employé ou l'équipe durant la période sélectionnée"
                                />
                                <KPICard
                                    title="Jours présents"
                                    value={reportsData.kpis.present_days_count || 0}
                                    unit="jours"
                                    color="green"
                                    icon="✅"
                                    description="Nombre de jours où l'employé a pointé et travaillé normalement"
                                />
                                <KPICard
                                    title="Jours absents"
                                    value={reportsData.kpis.absent_days_count || 0}
                                    unit="jours"
                                    color="red"
                                    icon="❌"
                                    description="Nombre de jours ouvrables où l'employé n'a pas pointé et était absent"
                                />
                                <KPICard
                                    title="Taux de retards"
                                    value={reportsData.kpis.late_arrivals_rate?.toFixed(1) || '0'}
                                    unit="%"
                                    color="orange"
                                    icon="⏰"
                                    description={`Pourcentage de jours avec retard (${reportsData.kpis.late_arrivals_count || 0} retards sur ${reportsData.kpis.present_days_count || 0} jours présents)`}
                                />
                                <KPICard
                                    title="Départs anticipés"
                                    value={reportsData.kpis.early_departures_count || 0}
                                    unit="fois"
                                    color="yellow"
                                    icon="🏃"
                                    description="Nombre de fois où l'employé est parti avant l'heure de fin - tolérance (30 min)"
                                />
                                <KPICard
                                    title="Jours incomplets"
                                    value={reportsData.kpis.incomplete_days_count || 0}
                                    unit="jours"
                                    color="purple"
                                    icon="⚠️"
                                    description="Jours où l'employé a oublié de pointer à l'entrée ou à la sortie"
                                />
                                <KPICard
                                    title="Total sorties"
                                    value={reportsData.kpis.total_exits_count || 0}
                                    unit="sorties"
                                    color="indigo"
                                    icon="🚪"
                                    description="Nombre total de pointages de sortie effectués durant la période"
                                />
                            </div>
                        </div>
                    )}
                </>
            )}
        </div>
    )
}
