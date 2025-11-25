interface InfoItemProps {
    label: string
    value: string
}

export default function InfoItem({ label, value }: InfoItemProps) {
    return (
        <div>
            <p className="text-sm text-[var(--c4)] mb-1">{label}</p>
            <p className="text-lg font-semibold">{value}</p>
        </div>
    )
}
