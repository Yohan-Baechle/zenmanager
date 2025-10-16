import ClockInOut from '../../components/features/clocks/ClockInOut'
import ClockHistory from "../../components/features/clocks/ClockHistory.tsx";
import { useAuth } from "../../hooks/useAuth.ts";
import { usersApi } from "../../api/users.api.ts";
import { clocksApi } from "../../api/clocks.api.ts";
import { useState, useEffect } from "react";
import type { Clock } from '../../types/clock.types'
import ClockRequest from "../../components/features/clocks/ClockRequest.tsx";
import ClockRequestModal from "../../components/features/clocks/ClockRequestModal.tsx";

export default function ClockPage() {
    const [clocks, setClocks] = useState<Clock[]>([])
    const [clocksRequest, setClocksRequest] = useState<Clock[]>([])
    const [loading, setLoading] = useState(false)
    const [isModalOpen, setIsModalOpen] = useState(false)
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
                        <ClockInOut onClockSuccess={fetchClocks} />
                        <ClockRequest
                            clocks={clocksRequest}
                            onOpenModal={() => setIsModalOpen(true)}
                        />
                    </div>
                    {loading ? <p>Chargement...</p> : <ClockHistory clocks={clocks} />}
                </div>
            </div>
            <ClockRequestModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                onSuccess={fetchClocksRequest}
            />
        </>
    )
}
