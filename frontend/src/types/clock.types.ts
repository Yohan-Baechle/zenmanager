export type ClockType = 'in' | 'out'

export interface Clock {
    id: number
    userId: number
    type: ClockType
    timestamp: string
    createdAt: string
}

export interface CreateClockDto {
    type: ClockType
}

export interface WorkingHoursSummary {
    userId: number
    totalHours: number
    dailyAverage: number
    weeklyAverage: number
    clocks: Clock[]
}
