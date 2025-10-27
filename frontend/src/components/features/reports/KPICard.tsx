interface KPICardProps {
    title: string
    value: number | string
    unit: string
    color: 'blue' | 'green' | 'red' | 'orange' | 'yellow' | 'purple' | 'indigo'
    icon: string
    description: string
}

export default function KPICard({ title, value, unit, color, icon, description }: KPICardProps) {
    const colorClasses = {
        blue: 'bg-blue-50 border-blue-200 text-blue-700',
        green: 'bg-green-50 border-green-200 text-green-700',
        red: 'bg-red-50 border-red-200 text-red-700',
        orange: 'bg-orange-50 border-orange-200 text-orange-700',
        yellow: 'bg-yellow-50 border-yellow-200 text-yellow-700',
        purple: 'bg-purple-50 border-purple-200 text-purple-700',
        indigo: 'bg-indigo-50 border-indigo-200 text-indigo-700',
    }

    return (
        <div className={`p-6 rounded-lg border-2 ${colorClasses[color]} transition-transform hover:scale-105`}>
            <div className="flex items-center justify-between mb-2">
                <p className="text-sm font-medium">{title}</p>
                <span className="text-2xl">{icon}</span>
            </div>
            <p className="text-3xl font-bold mb-2">{value}</p>
            <p className="text-xs opacity-75 mb-2">{unit}</p>
            <p className="text-xs opacity-90 leading-relaxed border-t pt-2 mt-2">
                {description}
            </p>
        </div>
    )
}
