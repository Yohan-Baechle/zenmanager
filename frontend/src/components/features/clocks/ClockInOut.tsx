import { useState, useEffect } from 'react'
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
            alert('Clock enregistré !')
            onClockSuccess?.()
        } catch (error) {
            alert(`Erreur : ${error instanceof Error ? error.message : 'Unknown error'}`)
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
