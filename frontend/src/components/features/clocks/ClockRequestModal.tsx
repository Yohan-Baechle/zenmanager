import Modal from "../../common/Modal.tsx";
import Button from "../../common/Button.tsx";
import {clocksApi} from "../../../api/clocks.api.ts";
import {useState} from "react";

interface ClockRequestModalProps {
    isOpen: boolean
    onClose: () => void
    onSuccess: () => void
}

export default function ClockRequestModal({ isOpen, onClose, onSuccess }: ClockRequestModalProps) {
    const [formData, setFormData] = useState({
        requestedTime: '',
        requestedStatus: true,
        reason: ''
    })

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault()

        try {
            await clocksApi.postClockRequest({
                requestedTime: formData.requestedTime,
                requestedStatus: formData.requestedStatus,
                reason: formData.reason,
                type: 'CREATE'
            })

            onClose()
            setFormData({
                requestedTime: '',
                requestedStatus: true,
                reason: ''
            })

            onSuccess()
            alert('Demande créée avec succès !')
        } catch (error) {
            alert(`Erreur : ${error instanceof Error ? error.message : 'Unknown error'}`)
        }
    }

    return (
        <Modal isOpen={isOpen} onClose={onClose} title="Nouvelle demande de pointage">
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
                    <Button type="button" onClick={onClose}>Annuler</Button>
                    <Button type="submit">Soumettre</Button>
                </div>
            </form>
        </Modal>
    )
}
