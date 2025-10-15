import type { Clock } from '../../../types/clock.types'
import Table from '../../common/Table'
import Card from '../../common/Card'
import { HistoryIcon } from "../../../assets/icons/history.tsx";

interface ClockHistoryProps {
    clocks: Clock[]
}

export default function ClockHistory({ clocks }: ClockHistoryProps) {
    const columns = [
        {
            header: 'Nom',
            accessor: (clock: Clock) => `${clock.owner.firstName} ${clock.owner.lastName}`
        },
        {
            header: 'Date',
            accessor: (clock: Clock) => new Date(clock.time).toLocaleDateString()
        },
        {
            header: 'Heure',
            accessor: (clock: Clock) => new Date(clock.time).toLocaleTimeString()
        },
        {
            header: 'Type',
            accessor: (clock: Clock) => (
                <span className={`px-2 py-1 rounded text-xs ${
                    clock.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }`}>
                  {clock.status ? 'Entrée' : 'Sortie'}
                </span>
            )
        },
    ]

    return (
        <Card
            title="Historique des pointages"
            icon={HistoryIcon}
        >
            <Table data={clocks} columns={columns} />
        </Card>
    )
}
