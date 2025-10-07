import type { Clock } from '../../../types/clock.types'
import Card from '../../common/Card'

interface ClockCardProps {
    clock: Clock
}

export default function ClockCard({ clock }: ClockCardProps) {
    return (
        <Card>
            <div className="flex justify-between items-center">
                <div>
                    <p className="text-sm text-gray-500">
                        {new Date(clock.timestamp).toLocaleDateString()}
                    </p>
                    <p className="text-lg font-semibold">
                        {new Date(clock.timestamp).toLocaleTimeString()}
                    </p>
                </div>
                <span className={`px-3 py-1 rounded ${
                    clock.type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }`}>
          {clock.type.toUpperCase()}
        </span>
            </div>
        </Card>
    )
}
