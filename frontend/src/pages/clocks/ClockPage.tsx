import ClockInOut from '../../components/features/clocks/ClockInOut'
import ClockHistory from "../../components/features/clocks/ClockHistory.tsx";
import { useAuth } from "../../hooks/useAuth.ts";
import { clocksApi } from "../../api/clocks.api.ts";
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
            const data = await clocksApi.getUserClocks(user.id)
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
        <div className="max-w-2xl">
            <h1 className="text-2xl font-bold mb-6">Pointeuse</h1>
            <ClockInOut />
            {loading ? <p>Chargement...</p> : <ClockHistory clocks={clocks} />}
        </div>
    )
}
