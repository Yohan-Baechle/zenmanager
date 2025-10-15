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

export interface ClockRequestDto {
    id: number
    requestedTime: string
    requestedStatus: boolean
    status: 'PENDING' | 'APPROVED' | 'REJECTED'
    reason: string
    user: User
    targetClock?: Clock
    reviewedBy?: User
    reviewedAt?: string
    createdAt: string
    updatedAt: string
}
