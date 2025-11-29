interface KPICardProps {
    title: string
    value: number | string
    unit: string
    description: string
}

export default function KPICard({ title, value, unit, description }: KPICardProps) {
    return (
        <div className="p-6 rounded-lg border-2 bg-[var(--c4)] text-[var(--c1)] transition-transform">
            <div className="flex items-center justify-between mb-2">
                <p className="text-sm font-medium">{title}</p>
            </div>
            <p className="text-3xl font-bold mb-2">{value} <span className="text-xs">{unit}</span></p>
            <p className="text-xs leading-relaxed border-t pt-2 mt-2">{description}</p>
        </div>
    )
}
