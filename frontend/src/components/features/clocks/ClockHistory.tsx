import type { Clock } from '../../../types/clock.types'
import Table from '../../common/Table'

interface ClockHistoryProps {
    clocks: Clock[]
}

export default function ClockHistory({ clocks }: ClockHistoryProps) {
    const columns = [
        {
            header: 'Date',
            accessor: (clock: Clock) => new Date(clock.timestamp).toLocaleDateString()
        },
        {
            header: 'Time',
            accessor: (clock: Clock) => new Date(clock.timestamp).toLocaleTimeString()
        },
        {
            header: 'Type',
            accessor: (clock: Clock) => (
                <span className={`px-2 py-1 rounded text-xs ${
                    clock.type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }`}>
          {clock.type.toUpperCase()}
        </span>
            )
        },
    ]

    return <Table data={clocks} columns={columns} />
}
