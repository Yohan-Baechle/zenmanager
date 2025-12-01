import ClockInOut from '../../components/features/clocks/ClockInOut'
import ClockHistory from "../../components/features/clocks/ClockHistory.tsx";
import { useAuth } from "../../hooks/useAuth.ts";
import { usersApi } from "../../api/users.api.ts";
import { clocksApi } from "../../api/clocks.api.ts";
import { useState, useEffect } from "react";
import type { Clock, ClockRequest } from '../../types/clock.types'
import ClockRequestComponent from "../../components/features/clocks/ClockRequest.tsx";
import ClockRequestModal from "../../components/features/clocks/ClockRequestModal.tsx";
import ClockRequestEditModal from "../../components/features/clocks/ClockRequestEditModal.tsx";
import Loader from "../../components/common/Loader.tsx";

export default function ClockPage() {
    const [clocks, setClocks] = useState<Clock[]>([])
    const [clocksRequest, setClocksRequest] = useState<ClockRequest[]>([])
    const [loading, setLoading] = useState(false)
    const [isModalOpen, setIsModalOpen] = useState(false)
    const [isEditModalOpen, setIsEditModalOpen] = useState(false)
    const [selectedRequest, setSelectedRequest] = useState<ClockRequest | null>(null)
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
            setClocksRequest(data as unknown as ClockRequest[])
        } catch (error) {
            alert(`Erreur : ${error instanceof Error ? error.message : 'Unknown error'}`)
        } finally {
            setLoading(false)
        }
    }

    const handleEditRequest = (request: ClockRequest) => {
        setSelectedRequest(request)
        setIsEditModalOpen(true)
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
                    {loading ? <Loader/> : (
                        <>
                            <div className="w-full 2xl:w-[570px] flex flex-col gap-4">
                                <ClockInOut onClockSuccess={fetchClocks}/>
                                <ClockRequestComponent
                                    clocks={clocksRequest}
                                    onOpenModal={() => setIsModalOpen(true)}
                                    onRefresh={fetchClocksRequest}
                                    onEdit={handleEditRequest}
                                />
                            </div>
                            <ClockHistory clocks={clocks}/>
                        </>
                    )}
                </div>
            </div>
            <ClockRequestModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                onSuccess={fetchClocksRequest}
            />
            {selectedRequest && (
                <ClockRequestEditModal
                    isOpen={isEditModalOpen}
                    onClose={() => {
                        setIsEditModalOpen(false)
                        setSelectedRequest(null)
                    }}
                    clockRequest={selectedRequest}
                    onSuccess={fetchClocksRequest}
                />
            )}
        </>
    )
}
