import { NavLink } from 'react-router-dom'

interface NavItemProps {
    to: string
    icon: string
    label: string
}

export default function NavItem({ to, icon, label }: NavItemProps) {
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
            <img src={icon} alt="" className="w-5 h-5" />
            <span className="font-medium">{label}</span>
        </NavLink>
    )
}
