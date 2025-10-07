import { useAuth } from '../../hooks/useAuth'
import { useNavigate } from 'react-router-dom'
import Button from '../common/Button'

export default function Header() {
    const { user, logout, isManager } = useAuth()
    const navigate = useNavigate()

    const handleLogout = async () => {
        await logout()
        navigate('/login')
    }

    return (
        <header className="bg-white shadow">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <h1 className="text-2xl font-bold text-gray-900">Time Manager</h1>
                <div className="flex items-center gap-4">
          <span className="text-sm text-gray-600">
            {user?.firstName} {user?.lastName}
              {isManager && <span className="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Manager</span>}
          </span>
                    <Button variant="secondary" onClick={handleLogout}>
                        Logout
                    </Button>
                </div>
            </div>
        </header>
    )
}
