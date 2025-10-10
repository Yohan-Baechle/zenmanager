import { useAuth } from '../../hooks/useAuth'
import UserBadge from '../common/UserBadge.tsx'
import NavItem from '../common/NavItem.tsx'
import SectionDivider from '../common/SectionDivider.tsx'

import { AlarmAddIcon } from '../../assets/icons/alarm-add'
import { AccountCircleIcon } from '../../assets/icons/account-circle'
import { DashboardIcon } from '../../assets/icons/dashboard'
import { SupervisorAccountIcon } from '../../assets/icons/supervisor-account'
import { ReportIcon } from '../../assets/icons/report'
import { AdminPanelSettingsIcon } from '../../assets/icons/admin-panel-settings'

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
                <NavItem to="/clock" icon={AlarmAddIcon} label="Clock In/Out" />
                <NavItem to="/clock/history" icon={AlarmAddIcon} label="Clock History" />
                <NavItem to="/profile" icon={AccountCircleIcon} label="Profile" />

                {isManager && (
                    <>
                        <SectionDivider label="Manager" />
                        <NavItem
                            to="/manager/dashboard"
                            icon={DashboardIcon}
                            label="Manager Dashboard"
                        />
                        <NavItem to="/users" icon={AccountCircleIcon} label="Users" />
                        <NavItem to="/teams" icon={SupervisorAccountIcon} label="Teams" />
                        <NavItem to="/reports" icon={ReportIcon} label="Reports" />
                        <NavItem to="/admin" icon={AdminPanelSettingsIcon} label="Administration" />
                    </>
                )}
            </nav>
        </aside>
    )
}
