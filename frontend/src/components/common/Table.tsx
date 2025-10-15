import type { ReactNode } from 'react'

interface Column<T> {
    header: string
    icon?: React.ComponentType<React.SVGProps<SVGSVGElement>>
    accessor: keyof T | ((row: T) => ReactNode)
    className?: string
}

interface TableProps<T> {
    data: T[]
    columns: Column<T>[]
}

export default function Table<T>({ data, columns }: TableProps<T>) {
    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-[var(--c2)]">
                <thead className="">
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
                {data.map((row, rowIndex) => (
                    <tr key={rowIndex} className="hover:bg-[var(--c2)]/25">
                        {columns.map((column, colIndex) => (
                            <td key={colIndex} className={`px-6 py-4 whitespace-nowrap ${column.className || ''}`}>
                                {typeof column.accessor === 'function'
                                    ? column.accessor(row)
                                    : String(row[column.accessor])}
                            </td>
                        ))}
                    </tr>
                ))}
                </tbody>
            </table>
        </div>
    )
}
