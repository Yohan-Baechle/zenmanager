import type { ButtonHTMLAttributes, ReactNode } from 'react'

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'primary'
    children: ReactNode
}

export default function Button({variant = 'primary', children, className = '', ...props}: ButtonProps) {
    const baseStyles = 'px-[18px] py-[14px] rounded-xl font-medium font-semibold transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed'

    const variants = {
        primary: 'bg-[var(--c4)] text-[var(--c1)] hover:bg-[var(--c5)]',
    }

    return (
        <button
            className={`${baseStyles} ${variants[variant]} ${className}`}
            {...props}
        >
            {children}
        </button>
    )
}
