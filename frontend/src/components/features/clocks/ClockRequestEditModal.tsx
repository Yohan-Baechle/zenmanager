import { useState, useEffect } from "react";
import { toast } from 'sonner'
import Modal from "../../common/Modal.tsx";
import Button from "../../common/Button.tsx";
import Input from "../../common/Input.tsx";
import { clocksApi } from "../../../api/clocks.api.ts";
import Checkbox from "../../common/Checkbox.tsx";
import Textarea from "../../common/Textarea.tsx";
import type { ClockRequest } from "../../../types/clock.types.ts";

interface ClockRequestEditModalProps {
    isOpen: boolean
    onClose: () => void
    clockRequest: ClockRequest
    onSuccess: () => void
}

export default function ClockRequestEditModal({ isOpen, onClose, clockRequest, onSuccess }: ClockRequestEditModalProps) {
    const [formData, setFormData] = useState({
        requestedTime: '',
        requestedStatus: true,
        reason: ''
    })

    useEffect(() => {
        if (clockRequest) {
            const date = new Date(clockRequest.requestedTime)
            const formattedDate = date.toISOString().slice(0, 16)

            setFormData({
                requestedTime: formattedDate,
                requestedStatus: clockRequest.requestedStatus,
                reason: clockRequest.reason || ''
            })
        }
    }, [clockRequest])

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault()

        try {
            await clocksApi.updateClockRequest(clockRequest.id, {
                requestedTime: formData.requestedTime,
                requestedStatus: formData.requestedStatus,
                reason: formData.reason
            })

            onClose()
            onSuccess()
            toast.success('Demande de pointage modifiée avec succès!')
        } catch (error: any) {
            const errorMessage = error.response?.data?.error || error.message || 'Erreur inconnue'
            toast.error(errorMessage)
        }
    }

    return (
        <Modal isOpen={isOpen} onClose={onClose} title="Modifier la demande de pointage">
            <form onSubmit={handleSubmit} className="space-y-4">
                <Input
                    type="datetime-local"
                    label="Date et heure"
                    floatingLabel={true}
                    value={formData.requestedTime}
                    onChange={(e) => setFormData({...formData, requestedTime: e.target.value})}
                    required
                />
                <div className="ml-2">
                    <Checkbox
                        label="Entrée (décocher pour Sortie)"
                        checked={formData.requestedStatus}
                        onChange={(e) => setFormData({...formData, requestedStatus: e.target.checked})}
                    />
                </div>
                <Textarea
                    label="Raison"
                    value={formData.reason}
                    onChange={(e) => setFormData({...formData, reason: e.target.value})}
                    required
                    rows={4}
                />

                <div className="flex gap-2 justify-end">
                    <Button type="button" onClick={onClose}>Annuler</Button>
                    <Button type="submit">Modifier</Button>
                </div>
            </form>
        </Modal>
    )
}
