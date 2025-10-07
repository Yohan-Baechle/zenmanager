import { useState } from 'react'
import Button from '../../common/Button'
import Card from '../../common/Card'
import { clocksApi } from '../../../api/clocks.api'

export default function ClockInOut() {
    const [loading, setLoading] = useState(false)
    const [lastClock, setLastClock] = useState<'in' | 'out' | null>(null)

    const handleClock = async (type: 'in' | 'out') => {
        setLoading(true)
        try {
            await clocksApi.create({ type })
            setLastClock(type)
            alert(`Successfully clocked ${type}!`)
        } catch (error) {
            alert(`Failed to clock ${type}: ${error instanceof Error ? error.message : 'Unknown error'}`)
        } finally {
            setLoading(false)
        }
    }

    return (
        <Card title="Clock In/Out">
            <div className="flex gap-4">
                <Button
                    onClick={() => handleClock('in')}
                    disabled={loading || lastClock === 'in'}
                    className="flex-1"
                >
                    Clock In
                </Button>
                <Button
                    onClick={() => handleClock('out')}
                    disabled={loading || lastClock === 'out'}
                    variant="secondary"
                    className="flex-1"
                >
                    Clock Out
                </Button>
            </div>
            {lastClock && (
                <p className="mt-4 text-sm text-gray-600">
                    Last action: Clocked {lastClock} at {new Date().toLocaleTimeString()}
                </p>
            )}
        </Card>
    )
}
