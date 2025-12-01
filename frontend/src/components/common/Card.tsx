import type { ReactNode } from 'react'

interface CardProps {
    title?: string
    icon?: React.ComponentType<React.SVGProps<SVGSVGElement>>
    description?: string
    children: ReactNode
    info?: string
    className?: string
}

export default function Card({ title, icon: Icon, description, children, info, className = '' }: CardProps) {
    return (
        <div className={`bg-[var(--c1)] border border-[var(--c2)] rounded-[20px] p-[28px] z-2 ${className}`}>
            {(title || Icon) &&
                <div className="flex flex-row gap-4 items-center mb-4">
                    {Icon && <Icon className="w-8 h-8"/>}
                    {title && <h1 className="text-2xl font-bold">{title}</h1>}
                </div>
            }
            {description && <p className="text-md text-[var(--c4)] mb-4">{description}</p>}
            {children}
            {info && <p className="mt-4 text-sm text-[var(--c4)]" dangerouslySetInnerHTML={{ __html: info }}></p>}
        </div>
    )
}
