import Card from '../../common/Card'
import {HistoryIcon} from "../../../assets/icons/history.tsx";
import {ArrowBackIosNewIcon} from "../../../assets/icons/arrow-back-ios-new.tsx";
import Button from "../../common/Button.tsx";
import Table from "../../common/Table.tsx";
import type {ClockRequest} from "../../../types/clock.types.ts";
import {useMemo, useState} from "react";
import {CalendarTodayIcon} from "../../../assets/icons/calendar-today.tsx";
import {ClockFarsightAnalogIcon} from "../../../assets/icons/clock-farsight-analog.tsx";
import {CompareArrowsIcon} from "../../../assets/icons/compare-arrows.tsx";
import {SettingsIcon} from "../../../assets/icons/settings.tsx";

interface ClockRequestProps {
    clocks: ClockRequest[]
    onOpenModal: () => void
}

export default function ClockRequest({ clocks, onOpenModal }: ClockRequestProps) {
    const [currentPage, setCurrentPage] = useState(1)
    const [startDate, setStartDate] = useState('')
    const [endDate, setEndDate] = useState('')
    const itemsPerPage = 4

    const filteredClocks = useMemo(() => {
        let filtered = [...clocks]

        if (startDate) {
            filtered = filtered.filter(clock =>
                new Date(clock.requestedTime) >= new Date(startDate)
            )
        }

        if (endDate) {
            filtered = filtered.filter(clock =>
                new Date(clock.requestedTime) <= new Date(endDate + 'T23:59:59')
            )
        }

        return filtered.sort((a, b) =>
            new Date(b.requestedTime).getTime() - new Date(a.requestedTime).getTime()
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
            header: 'Date',
            icon: CalendarTodayIcon,
            accessor: (clock: ClockRequest) => new Date(clock.requestedTime).toLocaleDateString()
        },
        {
            header: 'Heure',
            icon: ClockFarsightAnalogIcon,
            accessor: (clock: ClockRequest) => new Date(clock.requestedTime).toLocaleTimeString()
        },
        {
            header: 'Type',
            icon: CompareArrowsIcon,
            accessor: (clock: ClockRequest) => (
                <span className="text-sm font-medium text-[var(--c1)] bg-[var(--c4)] px-2 py-0.5 rounded-full inline-block w-fit">
                    {clock.requestedStatus ? '↓ Entrée' : '↑ Sortie'}
                </span>
            )
        },
        {
            header: 'Statut',
            icon: CompareArrowsIcon,
            accessor: (clock: ClockRequest) => (
                <span className="text-sm font-medium text-[var(--c1)] bg-[var(--c4)] px-2 py-0.5 rounded-full inline-block w-fit">
                    {clock.status ? 'En attente' : clock.status === 'APPROVED' ? 'Approuvé' : 'Rejeté'}
                </span>
            )
        },
        {
            header: '',
            accessor: () => (
                <div className="relative">
                    <SettingsIcon className="absolute top-1/2 -translate-y-1/2 -right-1 h-5 w-5 text-[var(--c5)] cursor-pointer "/>
                </div>
            )
        },
    ]

    return (
        <Card
            title="Demandes de pointage manuel"
            icon={HistoryIcon}
        >
            <div className="flex justify-between items-center mb-4">
                <div className="flex items-center text-sm text-[var(--c5)]">
                    <button
                        onClick={handlePrevious}
                        disabled={currentPage === 1}
                        className="p-2 rounded-s-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 cursor-pointer"
                    ><ArrowBackIosNewIcon className="h-5 w-5"/></button>
                    <div
                        className="font-medium h-9 p-2 flex items-center bg-[var(--c2)]/50 border-l border-r border-[var(--c2)]">
                        {currentPage}/{totalPages} Page{totalPages > 1 ? 's' : ''}
                    </div>
                    <button
                        onClick={handleNext}
                        disabled={currentPage === totalPages || totalPages === 0}
                        className="p-2 rounded-e-xl bg-[var(--c2)]/50 hover:bg-[var(--c2)]/75 cursor-pointer"
                    ><ArrowBackIosNewIcon className="h-5 w-5 rotate-180"/></button>
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
            <div className="flex justify-end mt-4">
                <Button onClick={onOpenModal}>Nouvelle demande</Button>
            </div>
        </Card>
    )
}
