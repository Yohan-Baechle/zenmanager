export type UserRole = 'employee' | 'manager'

export interface User {
    id: number
    firstName: string
    lastName: string
    email: string
    phoneNumber: string
    role: UserRole
    createdAt: string
    updatedAt: string
}

export interface CreateUserDto {
    firstName: string
    lastName: string
    email: string
    phoneNumber: string
    password: string
    role: UserRole
}

export interface UpdateUserDto {
    firstName?: string
    lastName?: string
    email?: string
    phoneNumber?: string
    password?: string
    role?: UserRole
}
