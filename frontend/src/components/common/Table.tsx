import type { ReactNode } from 'react'

interface Column<T extends object> {
    header: string
    icon?: React.ComponentType<React.SVGProps<SVGSVGElement>>
    accessor: keyof T | ((row: T) => ReactNode)
    className?: string
}

interface TableProps<T extends object> {
    data: T[]
    columns: Column<T>[]
    emptyMessage?: ReactNode
}

export default function Table<T extends object>({
                                                    data,
                                                    columns,
                                                    emptyMessage = 'Aucune donnée à afficher.'
                                                }: TableProps<T>) {
    return (
        <div className="w-full overflow-x-auto">
            <table className="w-full min-w-max divide-y divide-[var(--c2)]">
                <thead>
                <tr>
                    {columns.map((column, index) => {
                        const Icon = column.icon
                        return (
                            <th
                                key={index}
                                className="px-3 py-3 text-left text-[var(--c5)] uppercase tracking-wider"
                            >
                                {Icon && <Icon className="inline h-5 w-5 mr-1" />}
                                {column.header}
                            </th>
                        )
                    })}
                </tr>
                </thead>
                <tbody className="divide-y divide-[var(--c2)]">
                {data.length === 0 ? (
                    <tr>
                        <td
                            colSpan={columns.length}
                            className="px-6 py-4 text-center text-[var(--c5)] italic"
                        >
                            {emptyMessage}
                        </td>
                    </tr>
                ) : (
                    data.map((row, rowIndex) => (
                        <tr key={rowIndex} className="hover:bg-[var(--c2)]/25">
                            {columns.map((column, colIndex) => (
                                <td key={colIndex} className={`px-6 py-4 whitespace-nowrap ${column.className || ''}`}>
                                    {typeof column.accessor === 'function'
                                        ? column.accessor(row)
                                        : String((row as Record<keyof T, unknown>)[column.accessor] ?? '')}
                                </td>
                            ))}
                        </tr>
                    ))
                )}
                </tbody>
            </table>
        </div>
    )
}
