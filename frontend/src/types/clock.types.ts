import type { User } from './user.types'

export interface Clock {
    id: number
    time: string
    status: boolean
    owner: User
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
