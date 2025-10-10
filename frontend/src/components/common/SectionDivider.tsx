interface SectionDividerProps {
    label?: string
}

export default function SectionDivider({ label }: SectionDividerProps) {
    return (
        <div className="relative px-2 py-4">
            <div className="border-t border-[var(--c2)]"></div>
            {label &&
                <p className="absolute top-[50%] left-[50%] translate-y-[-50%] translate-x-[-50%] px-2 text-xs font-semibold text-[var(--c2)] bg-[var(--c1)] uppercase tracking-wider">
                    {label}
                </p>
            }
        </div>
    )
}
