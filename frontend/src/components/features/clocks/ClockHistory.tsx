import type { Clock } from '../../../types/clock.types'
import Table from '../../common/Table'
import Card from '../../common/Card'
import { HistoryIcon } from "../../../assets/icons/history.tsx";
import { AccountCircleIcon } from "../../../assets/icons/account-circle.tsx";
import { useState, useMemo } from 'react'
import { ArrowBackIosNewIcon } from "../../../assets/icons/arrow-back-ios-new.tsx";
import {CalendarTodayIcon} from "../../../assets/icons/calendar-today.tsx";
import {ClockFarsightAnalogIcon} from "../../../assets/icons/clock-farsight-analog.tsx";
import {CompareArrowsIcon} from "../../../assets/icons/compare-arrows.tsx";

interface ClockHistoryProps {
    clocks: Clock[]
}

export default function ClockHistory({ clocks }: ClockHistoryProps) {
    const [currentPage, setCurrentPage] = useState(1)
    const [startDate, setStartDate] = useState('')
    const [endDate, setEndDate] = useState('')
    const itemsPerPage = 8

    const filteredClocks = useMemo(() => {
        let filtered = [...clocks]

        if (startDate) {
            filtered = filtered.filter(clock =>
                new Date(clock.time) >= new Date(startDate)
            )
        }

        if (endDate) {
            filtered = filtered.filter(clock =>
                new Date(clock.time) <= new Date(endDate + 'T23:59:59')
            )
        }

        return filtered.sort((a, b) =>
            new Date(b.time).getTime() - new Date(a.time).getTime()
        )
    }, [clocks, startDate, endDate])

    const totalPages = Math.ceil(filteredClocks.length / itemsPerPage)
    const startIndex = (currentPage - 1) * itemsPerPage
    const paginatedClocks = filteredClocks.slice(startIndex, startIndex + itemsPerPage)

    const handlePrevious = () => {
        if (currentPage > 1) setCurrentPage(currentPage - 1)
    }

    const handleNext = () => {
        if (currentPage < totalPages) setCurrentPage(currentPage + 1)
    }

    const columns = [
        {
            header: 'Nom',
            icon: AccountCircleIcon,
            accessor: (clock: Clock) => `${clock.owner.firstName} ${clock.owner.lastName}`
        },
        {
            header: 'Date',
            icon: CalendarTodayIcon,
            accessor: (clock: Clock) => new Date(clock.time).toLocaleDateString()
        },
        {
            header: 'Heure',
            icon: ClockFarsightAnalogIcon,
            accessor: (clock: Clock) => new Date(clock.time).toLocaleTimeString()
        },
        {
            header: 'Type',
            icon: CompareArrowsIcon,
            accessor: (clock: Clock) => (
                <span className="text-sm font-medium text-[var(--c1)] bg-[var(--c4)] px-2 py-0.5 rounded-full inline-block w-fit">
                    {clock.status ? '↓ Entrée' : '↑ Sortie'}
                </span>
            )
        },
    ]

    return (
        <Card
            title="Historique des pointages"
            icon={HistoryIcon}
            className="min-w-[570px] overflow-hidden"
        >
            <div className="flex justify-between items-center mb-4">
                <div className="flex items-center text-sm text-[var(--c5)]">
                    <button
                        onClick={handlePrevious}
                        disabled={currentPage === 1}
                        className="p-2 rounded-s-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 cursor-pointer"
                    ><ArrowBackIosNewIcon className="h-5 w-5" /></button>
                    <div className="font-medium h-9 p-2 flex items-center bg-[var(--c2)]/50 border-l border-r border-[var(--c2)]">
                        {currentPage}/{totalPages} Page{totalPages > 1 ? 's' : ''}
                    </div>
                    <button
                        onClick={handleNext}
                        disabled={currentPage === totalPages || totalPages === 0}
                        className="p-2 rounded-e-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 cursor-pointer"
                    ><ArrowBackIosNewIcon className="h-5 w-5 rotate-180" /></button>
                </div>

                <div className="flex items-center text-sm text-[var(--c5)] bg-[var(--c2)]/50 p-1 rounded-xl">
                    <label className="flex items-center gap-2 ml-2">
                        <span>Du</span>
                        <input
                            type="date"
                            value={startDate}
                            onChange={(e) => {
                                setStartDate(e.target.value)
                                setCurrentPage(1)
                            }}
                            className="focus:outline-none bg-[var(--c2)] p-1 rounded-lg cursor-pointer"
                        />
                    </label>
                    <label className="flex items-center gap-2 ml-2">
                        <span>Au</span>
                        <input
                            type="date"
                            value={endDate}
                            onChange={(e) => {
                                setEndDate(e.target.value)
                                setCurrentPage(1)
                            }}
                            className="focus:outline-none bg-[var(--c2)] p-1 rounded-lg cursor-pointer"
                        />
                    </label>
                </div>
            </div>
            <Table data={paginatedClocks} columns={columns}/>
        </Card>
    )
}
