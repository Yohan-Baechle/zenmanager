import type { ReactNode } from 'react'

interface Column<T> {
    header: string
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
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                <tr>
                    {columns.map((column, index) => (
                        <th
                            key={index}
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            {column.header}
                        </th>
                    ))}
                </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                {data.map((row, rowIndex) => (
                    <tr key={rowIndex} className="hover:bg-gray-50">
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
