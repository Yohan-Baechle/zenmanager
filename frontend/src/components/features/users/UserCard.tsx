import type { User } from '../../../types/user.types'
import Card from '../../common/Card'

interface UserCardProps {
    user: User
}

export default function UserCard({ user }: UserCardProps) {
    return (
        <Card>
            <div className="space-y-2">
                <p className="text-lg font-semibold">
                    {user.firstName} {user.lastName}
                </p>
                <p className="text-sm text-gray-600">{user.email}</p>
                <p className="text-sm text-gray-600">{user.phoneNumber}</p>
                <span className={`inline-block px-2 py-1 text-xs rounded ${
                    user.role === 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                }`}>
          {user.role}
        </span>
            </div>
        </Card>
    )
}
