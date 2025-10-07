import Card from '../../common/Card'

interface KPICardProps {
    label: string
    value: string | number
    icon?: string
    color?: string
}

export default function KPICard({ label, value, icon, color = 'blue' }: KPICardProps) {
    return (
        <Card>
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm text-gray-500">{label}</p>
                    <p className={`text-2xl font-bold text-${color}-600`}>{value}</p>
                </div>
                {icon && <span className="text-3xl">{icon}</span>}
            </div>
        </Card>
    )
}
