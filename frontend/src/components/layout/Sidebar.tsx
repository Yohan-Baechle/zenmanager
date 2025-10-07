import { NavLink } from 'react-router-dom'
import { useAuth } from '../../hooks/useAuth'

export default function Sidebar() {
    const { isManager } = useAuth()

    const linkClasses = ({ isActive }: { isActive: boolean }) =>
        `block px-4 py-2 rounded-lg transition-colors ${
            isActive ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100'
        }`

    return (
        <aside className="w-64 bg-white shadow-lg h-[calc(100vh-73px)]">
            <nav className="p-4 space-y-2">
                <NavLink to="/dashboard" className={linkClasses}>
                    Dashboard
                </NavLink>
                <NavLink to="/clock" className={linkClasses}>
                    Clock In/Out
                </NavLink>
                <NavLink to="/clock/history" className={linkClasses}>
                    Clock History
                </NavLink>
                <NavLink to="/profile" className={linkClasses}>
                    Profile
                </NavLink>

                {isManager && (
                    <>
                        <div className="border-t border-gray-200 my-4"></div>
                        <p className="px-4 text-xs font-semibold text-gray-500 uppercase">Manager</p>
                        <NavLink to="/manager/dashboard" className={linkClasses}>
                            Manager Dashboard
                        </NavLink>
                        <NavLink to="/users" className={linkClasses}>
                            Users
                        </NavLink>
                        <NavLink to="/teams" className={linkClasses}>
                            Teams
                        </NavLink>
                        <NavLink to="/reports" className={linkClasses}>
                            Reports
                        </NavLink>
                        <NavLink to="/admin" className={linkClasses}>
                            Administration
                        </NavLink>
                    </>
                )}
            </nav>
        </aside>
    )
}
