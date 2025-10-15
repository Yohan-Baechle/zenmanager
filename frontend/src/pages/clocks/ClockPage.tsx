import ClockInOut from '../../components/features/clocks/ClockInOut'
import ClockHistory from "../../components/features/clocks/ClockHistory.tsx";
import { useAuth } from "../../hooks/useAuth.ts";
import { usersApi } from "../../api/users.api.ts";
import { useState, useEffect } from "react";
import type { Clock } from '../../types/clock.types'

export default function ClockPage() {
    const [clocks, setClocks] = useState<Clock[]>([])
    const [loading, setLoading] = useState(false)
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

    useEffect(() => {
        fetchClocks()
    }, [user])

    return (
        <div className="">
            <h1 className="text-2xl font-bold mb-6">Pointeuse</h1>
            <div className="flex flex-col 2xl:flex-row gap-4">
                <div className="w-full 2xl:w-[570px]">
                    <ClockInOut />
                </div>
                {loading ? <p>Chargement...</p> : <ClockHistory clocks={clocks} />}
            </div>
        </div>
    )
}
