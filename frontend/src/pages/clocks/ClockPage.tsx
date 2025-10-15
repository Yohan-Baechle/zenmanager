import ClockInOut from '../../components/features/clocks/ClockInOut'
import ClockHistory from "../../components/features/clocks/ClockHistory.tsx";
import { useAuth } from "../../hooks/useAuth.ts";
import { usersApi } from "../../api/users.api.ts";
import { clocksApi } from "../../api/clocks.api.ts";
import { useState, useEffect } from "react";
import type { Clock } from '../../types/clock.types'
import ClockRequest from "../../components/features/clocks/ClockRequest.tsx";
import Modal from "../../components/common/Modal.tsx";
import Button from "../../components/common/Button.tsx";

export default function ClockPage() {
    const [clocks, setClocks] = useState<Clock[]>([])
    const [clocksRequest, setClocksRequest] = useState<Clock[]>([])
    const [loading, setLoading] = useState(false)
    const [isModalOpen, setIsModalOpen] = useState(false)
    const [formData, setFormData] = useState({
        requestedTime: '',
        requestedStatus: true,
        reason: ''
    })
    const { user } = useAuth()

    const fetchClocks = async () => {
        if (!user) return

        setLoading(true)
        try {
            const data = await usersApi.getClocks(user.id)
            setClocks(data)
        } catch (error) {
            alert(`Erreur : ${error instanceof Error ? error.message : 'Unknown error'}`)
        } finally {
            setLoading(false)
        }
    }

    const fetchClocksRequest = async () => {
        if (!user) return

        setLoading(true)
        try {
            const data = await clocksApi.getClocksRequest()
            setClocksRequest(data)
        } catch (error) {
            alert(`Erreur : ${error instanceof Error ? error.message : 'Unknown error'}`)
        } finally {
            setLoading(false)
        }
    }

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault()

        try {
            await clocksApi.postClockRequest({
                requestedTime: formData.requestedTime,
                requestedStatus: formData.requestedStatus,
                reason: formData.reason,
                type: 'CREATE'
            })

            setIsModalOpen(false)
            setFormData({
                requestedTime: '',
                requestedStatus: true,
                reason: ''
            })

            await fetchClocksRequest()
            alert('Demande créée avec succès !')
        } catch (error) {
            alert(`Erreur : ${error instanceof Error ? error.message : 'Unknown error'}`)
        }
    }

    useEffect(() => {
        fetchClocks()
        fetchClocksRequest()
    }, [user])

    return (
        <>
            <div className="">
                <h1 className="text-2xl font-bold mb-6">Pointeuse</h1>
                <div className="flex flex-col 2xl:flex-row gap-4">
                    <div className="w-full 2xl:w-[570px] flex flex-col gap-4">
                        <ClockInOut />
                        <ClockRequest
                            clocks={clocksRequest}
                            onOpenModal={() => setIsModalOpen(true)}
                        />
                    </div>
                    {loading ? <p>Chargement...</p> : <ClockHistory clocks={clocks} />}
                </div>
            </div>

            <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title="Nouvelle demande de pointage">
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-[var(--c5)] mb-2">
                            Date et heure
                        </label>
                        <input
                            type="datetime-local"
                            value={formData.requestedTime}
                            onChange={(e) => setFormData({...formData, requestedTime: e.target.value})}
                            required
                            className="w-full p-2 rounded-lg bg-[var(--c2)] text-[var(--c5)] focus:outline-none"
                        />
                    </div>

                    <div>
                        <label className="flex items-center gap-2 text-sm font-medium text-[var(--c5)]">
                            <input
                                type="checkbox"
                                checked={formData.requestedStatus}
                                onChange={(e) => setFormData({...formData, requestedStatus: e.target.checked})}
                                className="rounded"
                            />
                            Entrée (décocher pour Sortie)
                        </label>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-[var(--c5)] mb-2">
                            Raison de la demande
                        </label>
                        <textarea
                            value={formData.reason}
                            onChange={(e) => setFormData({...formData, reason: e.target.value})}
                            required
                            rows={4}
                            className="w-full p-2 rounded-lg bg-[var(--c2)] text-[var(--c5)] focus:outline-none resize-none"
                        />
                    </div>

                    <div className="flex gap-2 justify-end">
                        <Button type="button" onClick={() => setIsModalOpen(false)}>Annuler</Button>
                        <Button type="submit">Soumettre</Button>
                    </div>
                </form>
            </Modal>
        </>
    )
}
