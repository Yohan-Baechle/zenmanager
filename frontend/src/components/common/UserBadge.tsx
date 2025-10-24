interface UserBadgeProps {
    firstName?: string
    lastName?: string
    role?: string
    className?: string
}

export default function UserBadge({ firstName, lastName, role, className = '' }: UserBadgeProps) {
    const initials = `${firstName?.[0] || ''}${lastName?.[0] || ''}`.toUpperCase()

    return (
        <div className={`flex items-center gap-3 bg-gradient-to-br from-[var(--c2)] to-[var(--c1)] px-4 py-2.5 rounded-xl border-2 border-[var(--c3)] shadow-sm hover:shadow-md transition-all duration-200 ${className}`}>
            <div className="relative">
                <div className="w-9 h-9 rounded-full bg-gradient-to-br from-[var(--c4)] to-[var(--c5)] flex items-center justify-center shadow-inner">
                    <span className="text-xs font-bold text-white">
                        {initials}
                    </span>
                </div>
                {role && (
                    <div className="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-[var(--c4)] rounded-full border-2 border-[var(--c1)] shadow-sm"
                         title={role}
                    />
                )}
            </div>

            <div className="flex flex-col gap-0.5">
                <span className="text-sm font-semibold text-[var(--c5)] leading-tight truncate">
                    {firstName} {lastName}
                </span>
                {role && (
                    <span className="text-[10px] font-medium text-[var(--c1)] bg-[var(--c4)] px-2 py-0.5 rounded-full inline-block w-fit shadow-sm">
                        {role}
                    </span>
                )}
            </div>
        </div>
    )
}
