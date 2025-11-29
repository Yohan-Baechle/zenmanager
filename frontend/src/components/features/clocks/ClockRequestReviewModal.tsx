import { useState, useEffect } from "react";
import { toast } from 'sonner'
import Modal from "../../common/Modal.tsx";
import Button from "../../common/Button.tsx";
import Input from "../../common/Input.tsx";
import { clocksApi } from "../../../api/clocks.api.ts";
import Checkbox from "../../common/Checkbox.tsx";
import Textarea from "../../common/Textarea.tsx";
import type { ClockRequest } from "../../../types/clock.types.ts";

interface ClockRequestReviewModalProps {
    isOpen: boolean
    onClose: () => void
    clockRequest: ClockRequest
    onSuccess: () => void
}

export default function ClockRequestReviewModal({ isOpen, onClose, clockRequest, onSuccess }: ClockRequestReviewModalProps) {
    const [formData, setFormData] = useState({
        requestedTime: '',
        requestedStatus: true,
        reason: ''
    })
    const [isSubmitting, setIsSubmitting] = useState(false)

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

    const handleApprove = async () => {
        if (isSubmitting) return
        setIsSubmitting(true)

        try {
            await clocksApi.approveClockRequest(clockRequest.id, {
                approvedTime: formData.requestedTime,
                approvedStatus: formData.requestedStatus
            })

            onClose()
            onSuccess()
            toast.success('Demande de pointage approuvée avec succès!')
        } catch (error: any) {
            const errorMessage = error.response?.data?.error || error.message || 'Erreur inconnue'
            toast.error(errorMessage)
        } finally {
            setIsSubmitting(false)
        }
    }

    const handleReject = async () => {
        if (isSubmitting) return
        setIsSubmitting(true)

        try {
            await clocksApi.rejectClockRequest(clockRequest.id, formData.reason || 'Demande rejetée')

            onClose()
            onSuccess()
            toast.success('Demande de pointage rejetée')
        } catch (error: any) {
            const errorMessage = error.response?.data?.error || error.message || 'Erreur inconnue'
            toast.error(errorMessage)
        } finally {
            setIsSubmitting(false)
        }
    }

    const handleUpdate = async (e: React.FormEvent) => {
        e.preventDefault()
        if (isSubmitting) return
        setIsSubmitting(true)

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
        } finally {
            setIsSubmitting(false)
        }
    }

    return (
        <Modal isOpen={isOpen} onClose={onClose} title="Gérer la demande de pointage">
            <form onSubmit={handleUpdate} className="space-y-4">
                <div className="bg-[var(--c2)]/30 p-4 rounded-lg mb-4">
                    <p className="text-sm text-[var(--c5)] mb-2">
                        <span className="font-semibold">Employé:</span> {clockRequest.user.firstName} {clockRequest.user.lastName}
                    </p>
                    <p className="text-sm text-[var(--c5)] mb-2">
                        <span className="font-semibold">Email:</span> {clockRequest.user.email}
                    </p>
                    <p className="text-sm text-[var(--c5)]">
                        <span className="font-semibold">Date de demande:</span> {new Date(clockRequest.createdAt).toLocaleString('fr-FR')}
                    </p>
                </div>

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

                <div className="flex gap-2 justify-end pt-4 border-t border-[var(--c3)]">
                    <Button
                        type="button"
                        onClick={handleReject}
                        variant="danger"
                        disabled={isSubmitting}
                    >
                        Rejeter
                    </Button>
                    <Button
                        type="submit"
                        variant="secondary"
                        disabled={isSubmitting}
                    >
                        Modifier
                    </Button>
                    <Button
                        type="button"
                        onClick={handleApprove}
                        variant="primary"
                        disabled={isSubmitting}
                    >
                        Approuver
                    </Button>
                </div>
            </form>
        </Modal>
    )
}
