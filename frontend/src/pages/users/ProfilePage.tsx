import { useAuth } from '../../hooks/useAuth'
import UserProfile from '../../components/features/users/UserProfile'
import Card from '../../components/common/Card'

export default function ProfilePage() {
    const { user } = useAuth()

    if (!user) return null

    return (
        <div className="max-w-2xl">
            <h1 className="text-2xl font-bold mb-6">Mon Profil</h1>
            <Card>
                <UserProfile data={user} />
            </Card>
        </div>
    )
}
