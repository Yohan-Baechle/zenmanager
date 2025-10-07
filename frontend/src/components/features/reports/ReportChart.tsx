import Card from '../../common/Card'

interface ReportChartProps {
    title: string
    data: unknown[]
}

export default function ReportChart({ title, data }: ReportChartProps) {
    return (
        <Card title={title}>
            <div className="h-64 flex items-center justify-center text-gray-400">
                <div className="text-center">
                    <p className="mb-2">Chart Component - Integrate with Recharts or Chart.js</p>
                    <pre className="text-xs text-left max-h-48 overflow-auto">
                        {JSON.stringify(data, null, 2)}
                    </pre>
                </div>
            </div>
        </Card>
    )
}
