interface SectionDividerProps {
    label?: string
    space?: string
}

export default function SectionDivider({ label, space }: SectionDividerProps) {
    return (
        <div className={`relative mx-2 ${space ? `my-${space}` : 'my-4'}`}>
            <div className="border-t border-[var(--c2)]"></div>
            {label &&
                <p className="absolute top-[50%] left-[50%] translate-y-[-50%] translate-x-[-50%] px-2 text-xs font-semibold text-[var(--c2)] bg-[var(--c1)] uppercase tracking-wider">
                    {label}
                </p>
            }
        </div>
    )
}
