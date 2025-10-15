export type ClockType = 'in' | 'out'

export interface Clock {
    id: number
    userId: number
    type: ClockType
    timestamp: string
    createdAt: string
}

export interface CreateClockDto {
    time: string
    userId: number
}

export interface WorkingHoursSummary {
    userId: number
    totalHours: number
    dailyAverage: number
    weeklyAverage: number
    clocks: Clock[]
}
