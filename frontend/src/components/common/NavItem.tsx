import { NavLink } from 'react-router-dom'

interface NavItemProps {
    to: string
    icon: React.ComponentType<React.SVGProps<SVGSVGElement>>
    label: string
}

export default function NavItem({ to, icon: Icon, label }: NavItemProps) {
    return (
        <NavLink
            to={to}
            className={({ isActive }) =>
                `flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ${
                    isActive
                        ? 'bg-[var(--c5)] text-white shadow-md'
                        : 'text-[var(--c4)] hover:bg-[var(--c2)] hover:text-[var(--c5)]'
                }`
            }
        >
            <Icon className="w-5 h-5" />
            <span className="font-medium">{label}</span>
        </NavLink>
    )
}
