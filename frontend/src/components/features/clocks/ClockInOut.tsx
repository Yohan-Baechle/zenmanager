import { useState } from 'react'
import Button from '../../common/Button'
import Card from '../../common/Card'
import { clocksApi } from '../../../api/clocks.api'
import { useAuth } from "../../../hooks/useAuth.ts";
import { AlarmAddIcon } from '../../../assets/icons/alarm-add.tsx'

export default function ClockInOut() {
    const [loading, setLoading] = useState(false)
    const { user } = useAuth()

    const handleClock = async () => {
        if (!user) return

        setLoading(true)
        try {
            await clocksApi.create({
                time: new Date().toISOString(),
                userId: user.id
            })
            alert('Clock enregistré !')
        } catch (error) {
            alert(`Erreur : ${error instanceof Error ? error.message : 'Unknown error'}`)
        } finally {
            setLoading(false)
        }
    }

    return (
        <Card
            title="Pointer"
            icon={AlarmAddIcon}
        >
            <Button
                onClick={handleClock}
                disabled={loading}
                className="w-full"
            >
                Pointer
            </Button>
        </Card>
    )
}
