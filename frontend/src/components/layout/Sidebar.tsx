import { useAuth } from '../../hooks/useAuth'
import UserBadge from '../common/UserBadge.tsx'
import NavItem from '../common/NavItem.tsx'
import SectionDivider from '../common/SectionDivider.tsx'

import DashboardIcon from '../../assets/icons/Dashboard.svg'
import ClockIcon from '../../assets/icons/AlarmAdd.svg'
import HistoryIcon from '../../assets/icons/AlarmAdd.svg'
import ProfileIcon from '../../assets/icons/AccountCircle.svg'
import ManagerDashboardIcon from '../../assets/icons/Dashboard.svg'
import UsersIcon from '../../assets/icons/AccountCircle.svg'
import TeamsIcon from '../../assets/icons/SupervisorAccount.svg'
import ReportsIcon from '../../assets/icons/Report.svg'
import AdminIcon from '../../assets/icons/AdminPanelSettings.svg'

export default function Sidebar() {
    const { user, isManager } = useAuth()

    return (
        <aside className="w-64 bg-[var(--c1)] shadow-lg h-[calc(100vh-73px)] border-r border-[var(--c3)] flex flex-col">
            <div className="p-4 pb-0 flex-shrink-0">
                <UserBadge
                    firstName={user?.firstName}
                    lastName={user?.lastName}
                    role={isManager ? 'Manager' : undefined}
                />

                <SectionDivider />
            </div>

            <nav className="flex-1 overflow-y-auto px-4 pb-4 space-y-1">
                <NavItem to="/dashboard" icon={DashboardIcon} label="Dashboard" />
                <NavItem to="/clock" icon={ClockIcon} label="Clock In/Out" />
                <NavItem to="/clock/history" icon={HistoryIcon} label="Clock History" />
                <NavItem to="/profile" icon={ProfileIcon} label="Profile" />

                {isManager && (
                    <>
                        <SectionDivider label="Manager" />
                        <NavItem
                            to="/manager/dashboard"
                            icon={ManagerDashboardIcon}
                            label="Manager Dashboard"
                        />
                        <NavItem to="/users" icon={UsersIcon} label="Users" />
                        <NavItem to="/teams" icon={TeamsIcon} label="Teams" />
                        <NavItem to="/reports" icon={ReportsIcon} label="Reports" />
                        <NavItem to="/admin" icon={AdminIcon} label="Administration" />
                    </>
                )}
            </nav>
        </aside>
    )
}
