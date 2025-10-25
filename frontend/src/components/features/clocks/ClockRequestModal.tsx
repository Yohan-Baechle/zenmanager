import { useState } from "react";
import { toast } from 'sonner'
import Modal from "../../common/Modal.tsx";
import Button from "../../common/Button.tsx";
import Input from "../../common/Input.tsx";
import { clocksApi } from "../../../api/clocks.api.ts";
import Checkbox from "../../common/Checkbox.tsx";
import Textarea from "../../common/Textarea.tsx";

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
            toast.success('Demande de pointage créée avec succès!')
        } catch (error: any) {
            const errorMessage = error.response?.data?.error || error.message || 'Erreur inconnue'
            toast.error(errorMessage)
        }
    }

    return (
        <Modal isOpen={isOpen} onClose={onClose} title="Nouvelle demande de pointage">
            <form onSubmit={handleSubmit} className="space-y-4">
                <Input
                    type="datetime-local"
                    label="Date et heure"
                    floatingLabel={true}
                    value={formData.requestedTime}
                    onChange={(e) => setFormData({...formData, requestedTime: e.target.value})}
                    required
                />
                <div className="ml-2"><Checkbox
                    label="Entrée (décocher pour Sortie)"
                    checked={formData.requestedStatus}
                    onChange={(e) => setFormData({...formData, requestedStatus: e.target.checked})}
                /></div>
                <Textarea
                    label="Raison"
                    value={formData.reason}
                    onChange={(e) => setFormData({...formData, reason: e.target.value})}
                    required
                    rows={4}
                />

                <div className="flex gap-2 justify-end">
                    <Button type="button" onClick={onClose}>Annuler</Button>
                    <Button type="submit">Soumettre</Button>
                </div>
            </form>
        </Modal>
    )
}
