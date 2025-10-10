import { useState, useRef, useEffect } from 'react'

export interface DropdownOption {
    label: string
    icon?: string
    onClick: () => void
    variant?: 'default' | 'danger' | 'success'
}

interface DropdownMenuProps {
    trigger?: {
        text?: string
        icon?: string
        className?: string
    }
    options: DropdownOption[]
    align?: 'left' | 'right'
    className?: string
}

export default function DropdownMenu({
                                         trigger,
                                         options,
                                         align = 'right',
                                         className = ''
                                     }: DropdownMenuProps) {
    const [isOpen, setIsOpen] = useState(false)
    const dropdownRef = useRef<HTMLDivElement>(null)

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setIsOpen(false)
            }
        }

        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside)
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside)
        }
    }, [isOpen])

    const handleOptionClick = (option: DropdownOption) => {
        option.onClick()
        setIsOpen(false)
    }

    return (
        <div className={`relative ${className}`} ref={dropdownRef}>
            <button
                onClick={() => setIsOpen(!isOpen)}
                className={`flex items-center gap-2 px-4 py-2 rounded-lg bg-[var(--c4)] hover:bg-[var(--c5)] transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer ${trigger?.className || ''}`}
            >
                {trigger?.icon && (
                    <img src={trigger.icon} alt="" className="w-5 h-5" />
                )}
                {trigger?.text && (
                    <span className="text-sm font-medium text-[var(--c1)]">
                        {trigger.text}
                    </span>
                )}
                <svg
                    className={`w-4 h-4 text-[var(--c1)] transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {isOpen && (
                <div
                    className={`absolute rounded-xl ${align === 'right' ? 'right-0 rounded-tr-none' : 'left-0 rounded-tl-none'} mt-2 w-56 bg-[var(--c1)] border-1 border-[var(--c3)] shadow-lg overflow-hidden z-50 animate-fadeIn`}
                >
                    <div className="py-1">
                        {options.map((option, index) => (
                            <button
                                key={index}
                                onClick={() => handleOptionClick(option)}
                                className={`w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-[var(--c5)] hover:bg-[var(--c2)] font-medium transition-colors duration-150 cursor-pointer`}
                            >
                                {option.icon && (
                                    <img src={option.icon} alt="" className="w-5 h-5" />
                                )}
                                <span>{option.label}</span>
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    )
}
