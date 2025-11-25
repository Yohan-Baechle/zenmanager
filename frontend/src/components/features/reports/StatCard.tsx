interface StatCardProps {
    label: string
    value: string | number
}

export default function StatCard({ label, value }: StatCardProps) {
    return (
        <div className="p-4 rounded-[14px] border-2 bg-[var(--c1)] border-[var(--c3)] text-[var(--c5)]">
            <p className="text-sm mb-2 opacity-80">{label}</p>
            <p className="text-3xl font-bold">{value}</p>
        </div>
    )
}
