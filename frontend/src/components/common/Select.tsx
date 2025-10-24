import type { SelectHTMLAttributes } from 'react'
import { forwardRef } from 'react'

interface SelectOption {
    value: string
    label: string
}

interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
    label?: string
    options: SelectOption[]
    error?: string
}

const Select = forwardRef<HTMLSelectElement, SelectProps>(
    ({ label, options, error, ...props }, ref) => {
        return (
            <div className="w-full">
                {label && (
                    <label className="block text-sm font-medium text-[var(--c5)] mb-2">
                        {label}
                    </label>
                )}
                <select
                    ref={ref}
                    className="w-full border border-[var(--c3)] bg-[var(--c1)] text-[var(--c5)] rounded-[14px] py-[14px] px-[14px] outline-none text-base transition-[border-color,outline-color] duration-150 focus:border-[var(--c4)] focus:border-[3px] cursor-pointer"
                    {...props}
                >
                    {options.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
                {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
            </div>
        )
    }
)

Select.displayName = 'Select'

export default Select
