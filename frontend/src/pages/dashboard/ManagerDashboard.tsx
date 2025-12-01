import { useEffect, useState } from 'react'
import { teamsApi } from '../../api/teams.api'
import type { Team } from '../../types/team.types'
import KPICard from '../../components/features/reports/KPICard'
import TeamCard from '../../components/features/teams/TeamCard'
import Loader from '../../components/common/Loader'
import Card from '../../components/common/Card'
import PdfPreviewModal from '../../components/common/PdfPreviewModal'
import { SearchIcon } from '../../assets/icons/search'
import { exportClockingPdf } from '../../api/exports'
import UserSearchModal from '../../components/features/teams/UserSearchModal'
import Button from '../../components/common/Button'

export default function ManagerDashboard() {
    const [teams, setTeams] = useState<Team[]>([])
    const [loading, setLoading] = useState(true)
    const [startDate, setStartDate] = useState('')
    const [endDate, setEndDate] = useState('')
    const [selectedTeamId, setSelectedTeamId] = useState<number | null>(null)
    const [exportingPdf, setExportingPdf] = useState(false)
    const [exportError, setExportError] = useState<string | null>(null)
    const [previewModalOpen, setPreviewModalOpen] = useState(false)
    const [pdfBlob, setPdfBlob] = useState<Blob | null>(null)
    const [pdfFilename, setPdfFilename] = useState('')
    const [isUserSearchModalOpen, setIsUserSearchModalOpen] = useState(false)

    useEffect(() => {
        loadTeams()
    }, [])

    const loadTeams = async () => {
        try {
            const response = await teamsApi.getAll()
            setTeams(response.data)
            if (response.data.length > 0 && !selectedTeamId) {
                setSelectedTeamId(response.data[0].id)
            }
        } catch (error) {
            console.error('Failed to load teams', error)
        } finally {
            setLoading(false)
        }
    }

    const translateFieldName = (fieldName: string): string => {
        const translations: Record<string, string> = {
            start_date: 'Date de début',
            end_date: 'Date de fin',
            team_id: 'Équipe',
            user_id: 'Utilisateur',
        };
        return translations[fieldName] || fieldName;
    };

    const translateErrorMessage = (message: string): string => {
        const translationMap: Record<string, string> = {
            'Date range cannot exceed 1 year': 'L\'intervalle de dates ne peut pas dépasser 1 an.',
            'end_date must be after start_date': 'La date de fin doit être postérieure à la date de début.',
            'Dates cannot be in the future': 'Les dates ne peuvent pas être dans le futur.',
            'must be a valid date': 'doit être une date valide (format: YYYY-MM-DD).',
            'must be either "pdf" or "xlsx"': 'doit être "pdf" ou "xlsx".',
            'This value should be positive.': 'Cette valeur doit être positive.',
            'Not Found': 'Non trouvé',
            'Managers must specify a team_id parameter': 'Les managers doivent spécifier un paramètre team_id',
            'Access denied. You can only export data for teams you manage.': 'Accès refusé. Vous ne pouvez exporter que les données des équipes que vous gérez.',
        };

        if (translationMap[message]) {
            return translationMap[message];
        }

        for (const key in translationMap) {
            if (message.includes(key)) {
                return translationMap[key];
            }
        }

        const match = message.match(/This value should be of type (\w+)./);
        if (match) {
            const type = match[1];
            return `Cette valeur doit être de type ${type}.`;
        }

        return message;
    };

    const handleExportError = (error: any, defaultMessage: string) => {
        let errorMessage = defaultMessage;

        if (error.response?.data) {
            const data = error.response.data;

            if (data.errors && typeof data.errors === 'object') {
                const errorList = Object.entries(data.errors)
                    .map(([field, message]) => {
                        const translatedField = translateFieldName(field);
                        const translatedMessage = Array.isArray(message)
                            ? message.map(msg => translateErrorMessage(msg as string)).join(', ')
                            : translateErrorMessage(message as string);
                        return `${translatedField}: ${translatedMessage}`;
                    })
                    .join('\n');
                errorMessage = errorList;
            }
            else if (data.message && typeof data.message === 'string') {
                errorMessage = translateErrorMessage(data.message);
            }
            else if (data.error && typeof data.error === 'string') {
                errorMessage = translateErrorMessage(data.error);
            }
        }
        else if (error.message) {
            errorMessage = error.message;
        }

        setExportError(errorMessage);
    };

    const handleExportPdf = async (download: boolean = true) => {
        setExportingPdf(true)
        setExportError(null)
        try {
            const { blob, filename } = await exportClockingPdf({
                startDate: startDate || undefined,
                endDate: endDate || undefined,
                teamId: selectedTeamId || undefined,
            }, download)

            if (!download) {
                setPdfBlob(blob)
                setPdfFilename(filename)
                setPreviewModalOpen(true)
            }
        } catch (error: any) {
            handleExportError(error, 'Erreur lors de la génération du fichier PDF');
        } finally {
            setExportingPdf(false)
        }
    }



    if (loading) return <Loader />

    return (
        <div className="space-y-6">
            <h1 className="text-2xl font-bold">Manager Dashboard</h1>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <KPICard
                    title="Total Teams"
                    value={teams.length}
                    description=""
                    unit=""
                />
                <KPICard
                    title="Total Members"
                    value={teams.reduce((acc, team) => acc + (team.employees?.length || 0), 0)}
                    description=""
                    unit=""
                />
                <KPICard
                    title="Active Projects"
                    value="0"
                    description=""
                    unit=""
                />
            </div>

            <Card title="Exporter les données de pointage">
                {exportError && (
                    <div className="mb-4 p-4 bg-red-100 text-red-700 rounded-xl text-sm">
                        <div className="font-semibold mb-2">❌ Erreur</div>
                        <div className="whitespace-pre-line">{exportError}</div>
                    </div>
                )}

                <div className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium mb-2">
                            Équipe
                        </label>
                        <select
                            value={selectedTeamId || ''}
                            onChange={(e) => setSelectedTeamId(e.target.value ? Number(e.target.value) : null)}
                            className="w-full px-4 py-2 rounded-xl border border-[var(--c2)] bg-[var(--c1)] focus:outline-none focus:border-[var(--c4)]"
                        >
                            <option value="">Toutes les équipes</option>
                            {teams.map((team) => (
                                <option key={team.id} value={team.id}>
                                    {team.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium mb-2">
                                Date de début
                            </label>
                            <input
                                type="date"
                                value={startDate}
                                onChange={(e) => setStartDate(e.target.value)}
                                className="w-full px-4 py-2 rounded-xl border border-[var(--c2)] bg-[var(--c1)] focus:outline-none focus:border-[var(--c4)]"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-2">
                                Date de fin
                            </label>
                            <input
                                type="date"
                                value={endDate}
                                onChange={(e) => setEndDate(e.target.value)}
                                className="w-full px-4 py-2 rounded-xl border border-[var(--c2)] bg-[var(--c1)] focus:outline-none focus:border-[var(--c4)]"
                            />
                        </div>
                    </div>

                    <div className="flex justify-center space-y-3 pt-2">
                        <button
                            onClick={() => handleExportPdf(false)}
                            disabled={exportingPdf}
                            className="px-22 py-3 rounded-xl font-medium font-semibold transition-colors bg-[var(--c2)] text-[var(--c5)] hover:bg-[var(--c2)]/75 disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Prévisualiser le PDF"
                        >
                            <SearchIcon className="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </Card>

            <div>
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-xl font-semibold">My Teams</h2>
                    <Button
                        onClick={() => setIsUserSearchModalOpen(true)}
                        disabled={!selectedTeamId}
                        className="!py-2 !px-4 text-sm"
                    >
                        Gérer les membres
                    </Button>
                </div>
                {teams.length === 0 ? (
                    <div className="bg-[var(--c1)] border border-[var(--c2)] rounded-[20px] p-[28px] text-center">
                        <p className="text-[var(--c5)]">
                            Vous ne gérez aucune équipe actuellement.
                        </p>
                        <p className="text-sm text-[var(--c5)] mt-2">
                            Contactez un administrateur pour vous assigner des équipes.
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {teams.map((team) => (
                            <TeamCard key={team.id} team={team} />
                        ))}
                    </div>
                )}
            </div>

            <PdfPreviewModal
                isOpen={previewModalOpen}
                onClose={() => setPreviewModalOpen(false)}
                pdfBlob={pdfBlob}
                filename={pdfFilename}
                exportParams={{
                    startDate: startDate || undefined,
                    endDate: endDate || undefined,
                    teamId: selectedTeamId || undefined,
                }}
                onExportError={handleExportError}
            />

            {selectedTeamId && (
                <UserSearchModal
                    isOpen={isUserSearchModalOpen}
                    onClose={() => setIsUserSearchModalOpen(false)}
                    teamId={selectedTeamId}
                    onUserUpdated={loadTeams}
                />
            )}

        </div>
    )
}
