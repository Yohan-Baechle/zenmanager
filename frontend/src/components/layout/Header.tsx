import { useAuth } from '../../hooks/useAuth'
import { useNavigate } from 'react-router-dom'
import DropdownMenu from '../common/DropdownMenu'
import type { DropdownOption } from '../common/DropdownMenu'
import { useMediaQuery } from '../../hooks/useMediaQuery.ts'
import { LogoIcon } from '../../assets/logo'
import { Plante1Icon } from '../../assets/plante1'
import { Plante2Icon } from '../../assets/plante2'
import { AccountCircleIcon } from '../../assets/icons/account-circle'
import { SettingsIcon } from '../../assets/icons/settings'
import { LogoutIcon } from '../../assets/icons/logout'
import { useSidebar } from '../../hooks/useSidebar'

export default function Header() {
    const { logout } = useAuth()
    const navigate = useNavigate()
    const { toggleSidebar } = useSidebar()
    const deviceType = useMediaQuery()

    const handleLogout = async () => {
        await logout()
        navigate('/login')
    }

    const handleProfile = () => {
        navigate('/profile')
    }

    const menuOptions: DropdownOption[] = [
        {
            label: 'Mon profil',
            icon: AccountCircleIcon,
            onClick: handleProfile,
        },
        {
            label: 'Déconnexion',
            icon: LogoutIcon,
            onClick: handleLogout,
            variant: 'danger',
        },
    ]

    return (
        <header className="relative bg-[var(--c1)] shadow-md">
            <div className="absolute inset-0 overflow-hidden pointer-events-none">
                <Plante1Icon className="absolute top-0 right-0 translate-x-[30%] -translate-y-[30%] -rotate-[15deg] w-[300px] h-auto opacity-[0.08] select-none" />
                <Plante2Icon className="absolute bottom-0 left-0 -translate-x-[30%] translate-y-[30%] rotate-[160deg] w-[300px] h-auto opacity-[0.08] select-none" />
            </div>

            <div className="relative  mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <div className="flex items-center gap-3">
                    <button
                        onClick={toggleSidebar}
                        className="p-2 hover:bg-[var(--c2)] rounded transition-colors"
                        aria-label="Toggle sidebar"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <LogoIcon className="w-10 h-10"/>
                    <h1 className="text-2xl font-bold text-[var(--c5)]">Time Manager</h1>
                </div>

                {deviceType !== 'mobile' && (
                    <div className="flex items-center gap-4">
                        <DropdownMenu
                            trigger={{
                                text: 'Menu',
                                icon: SettingsIcon,
                            }}
                            options={menuOptions}
                            align="right"
                        />
                    </div>
                )}
            </div>
        </header>
    )
}
