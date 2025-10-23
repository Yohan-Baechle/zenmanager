import { useAuth } from '../../hooks/useAuth'
import { useSidebar } from '../../hooks/useSidebar'
import UserBadge from '../common/UserBadge.tsx'
import NavItem from '../common/NavItem.tsx'
import SectionDivider from '../common/SectionDivider.tsx'

import { DashboardIcon } from '../../assets/icons/dashboard'
import { AlarmAddIcon } from '../../assets/icons/alarm-add'
import { AccountCircleIcon } from '../../assets/icons/account-circle'
import { PersonIcon } from '../../assets/icons/person'
import { SupervisorAccountIcon } from '../../assets/icons/supervisor-account'
import { ReportIcon } from '../../assets/icons/report'
import { AdminPanelSettingsIcon } from '../../assets/icons/admin-panel-settings'

export default function Sidebar() {
    const { user, role } = useAuth()
    const { sidebarState } = useSidebar()

    const widthClasses = {
        open: 'w-64',
        semi: 'w-20',
        closed: 'w-0'
    }

    if (sidebarState === 'closed') {
        return null
    }

    const isSemi = sidebarState === 'semi'

    return (
        <aside className={`${widthClasses[sidebarState]} bg-[var(--c1)] shadow-lg h-[calc(100vh-73px)] border-r border-[var(--c3)] flex flex-col transition-all duration-300 overflow-hidden`}>
            {!isSemi && (
                <div className="p-4 pb-0 flex-shrink-0">
                    <UserBadge
                        firstName={user?.firstName}
                        lastName={user?.lastName}
                        role={role === 'manager' ? 'Manager' : role === 'admin' ? 'Admin' : 'Employee'}
                    />
                    <SectionDivider />
                </div>
            )}

            <nav className={`flex-1 overflow-y-auto px-4 pb-4 space-y-1 ${isSemi ? 'py-4' : ''}`}>
                <NavItem to="/dashboard" icon={DashboardIcon} label="Tableau de Bord" iconOnly={isSemi} />
                <NavItem to="/clock" icon={AlarmAddIcon} label="Pointeuse" iconOnly={isSemi} />
                <NavItem to="/profile" icon={AccountCircleIcon} label="Mon Profil" iconOnly={isSemi} />

                {role === 'manager' && (
                    <>
                        {!isSemi && <SectionDivider label="Manager" />}
                        {isSemi && <SectionDivider />}
                        <NavItem to="/manager/dashboard" icon={DashboardIcon} label="Manager Dashboard" iconOnly={isSemi} />
                        <NavItem to="/users" icon={PersonIcon} label="Utilisateurs" iconOnly={isSemi} />
                        <NavItem to="/teams" icon={SupervisorAccountIcon} label="Équipes" iconOnly={isSemi} />
                        <NavItem to="/reports" icon={ReportIcon} label="Reports" iconOnly={isSemi} />
                    </>
                )}

                {role === 'admin' && (
                    <>
                        {!isSemi && <SectionDivider label="Administration" />}
                        {isSemi && <SectionDivider />}
                        <NavItem to="/reports" icon={ReportIcon} label="Reports" iconOnly={isSemi} />
                        <NavItem to="/admin" icon={AdminPanelSettingsIcon} label="Administration" iconOnly={isSemi} />
                    </>
                )}
            </nav>
        </aside>
    )
}
