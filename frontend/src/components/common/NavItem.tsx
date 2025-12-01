import { NavLink } from 'react-router-dom'

interface NavItemProps {
    to: string
    icon: React.ComponentType<React.SVGProps<SVGSVGElement>>
    label: string
    iconOnly?: boolean
}

export default function NavItem({ to, icon: Icon, label, iconOnly = false }: NavItemProps) {
    return (
        <NavLink
            to={to}
            className={({ isActive }) =>
                `flex items-center gap-3 px-[13.5px] py-3 rounded-lg transition-all duration-200 ${
                    isActive
                        ? 'bg-[var(--c5)] text-white shadow-md'
                        : 'text-[var(--c4)] hover:bg-[var(--c2)] hover:text-[var(--c5)]'
                }`
            }
            title={iconOnly ? label : undefined}
        >
            <Icon className="w-5 h-5 flex-shrink-0" />
            {!iconOnly && <span className="font-medium truncate">{label}</span>}
        </NavLink>
    )
}
