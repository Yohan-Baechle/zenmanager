import type { Clock } from '../../../types/clock.types'
import Table from '../../common/Table'
import Card from '../../common/Card'
import { HistoryIcon } from "../../../assets/icons/history.tsx";
import { AccountCircleIcon } from "../../../assets/icons/account-circle.tsx";

interface ClockHistoryProps {
    clocks: Clock[]
}

export default function ClockHistory({ clocks }: ClockHistoryProps) {
    const columns = [
        {
            header: 'Nom',
            icon: AccountCircleIcon,
            accessor: (clock: Clock) => `${clock.owner.firstName} ${clock.owner.lastName}`
        },
        {
            header: 'Date',
            icon: HistoryIcon,
            accessor: (clock: Clock) => new Date(clock.time).toLocaleDateString()
        },
        {
            header: 'Heure',
            icon: HistoryIcon,
            accessor: (clock: Clock) => new Date(clock.time).toLocaleTimeString()
        },
        {
            header: 'Type',
            icon: HistoryIcon,
            accessor: (clock: Clock) => (
                <span className={`text-sm font-medium text-[var(--c1)] bg-[var(--c4)] px-2 py-0.5 rounded-full inline-block w-fit ${
                    clock.status ? '' : ''
                }`}>
                  {clock.status ? '↓ Entrée' : '↑ Sortie'}
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
