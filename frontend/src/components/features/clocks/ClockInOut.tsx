import { useState, useEffect } from 'react'
import { toast } from 'sonner'
import Button from '../../common/Button'
import Card from '../../common/Card'
import { clocksApi } from '../../../api/clocks.api'
import { useAuth } from "../../../hooks/useAuth.ts";
import { AlarmAddIcon } from '../../../assets/icons/alarm-add.tsx'

interface ClockInOutProps {
    onClockSuccess?: () => void
}

export default function ClockInOut({ onClockSuccess }: ClockInOutProps) {
    const [loading, setLoading] = useState(false)
    const [currentTime, setCurrentTime] = useState(new Date())
    const { user } = useAuth()

    useEffect(() => {
        const interval = setInterval(() => {
            setCurrentTime(new Date())
        }, 1000)

        return () => clearInterval(interval)
    }, [])

    const handleClock = async () => {
        if (!user) return

        setLoading(true)
        try {
            await clocksApi.create({
                time: new Date().toISOString(),
                userId: user.id
            })
            toast.success('Pointage enregistré avec succès!')
            onClockSuccess?.()
        } catch (error: any) {
            const errorMessage = error.response?.data?.error || error.message || 'Erreur inconnue'
            toast.error(errorMessage)
        } finally {
            setLoading(false)
        }
    }

    return (
        <Card
            title={`Pointer ${currentTime.toLocaleTimeString()}`}
            icon={AlarmAddIcon}
        >
            <Button
                onClick={handleClock}
                disabled={loading}
                className="w-full"
            >
                Cliquer ici pour pointer
            </Button>
        </Card>
    )
}
