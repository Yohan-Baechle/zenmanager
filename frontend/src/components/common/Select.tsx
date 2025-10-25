import type { SelectHTMLAttributes } from 'react'
import React, { forwardRef, useEffect, useRef, useState } from 'react'

interface SelectOption {
    value: string
    label: string
}

interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
    label?: string
    options: SelectOption[]
    error?: string
    floatingLabel?: boolean
}

const Select = forwardRef<HTMLSelectElement, SelectProps>(
    ({ label, options, error, className, floatingLabel = false, ...props }, ref) => {
        const innerRef = useRef<HTMLSelectElement | null>(null)
        const combinedRef = (node: HTMLSelectElement | null) => {
            innerRef.current = node
            if (typeof ref === 'function') ref(node)
            else if (ref) (ref as React.MutableRefObject<HTMLSelectElement | null>).current = node
        }

        const [isFocused, setIsFocused] = useState(false)
        const [hasValue, setHasValue] = useState(!!props.value || !!props.defaultValue)

        useEffect(() => {
            const v = innerRef.current?.value ?? ''
            if (v.length > 0) setHasValue(true)
        }, [])

        useEffect(() => {
            if (props.value !== undefined) {
                const v = String(props.value ?? '')
                setHasValue(v.length > 0)
            }
        }, [props.value])

        const handleFocus = () => setIsFocused(true)
        const handleBlur = (e: React.FocusEvent<HTMLSelectElement>) => {
            setIsFocused(false)
            setHasValue((e.target.value ?? '').length > 0)
            props.onBlur?.(e)
        }
        const handleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
            setHasValue((e.target.value ?? '').length > 0)
            props.onChange?.(e)
        }

        const isLabelFloating = floatingLabel || isFocused || hasValue

        return (
            <div className="w-full relative">
                <select
                    ref={combinedRef}
                    className={`peer w-full border border-[var(--c3)] bg-[var(--c1)] text-[var(--c5)] rounded-[14px] py-[14px] px-[14px] outline-none text-base transition-[border-color,outline-color,transform] duration-150
                     active:translate-y-px active:duration-75 focus:border-[var(--c4)] focus:border-[3px] cursor-pointer ${className || ''}`}
                    onFocus={handleFocus}
                    onBlur={handleBlur}
                    onChange={handleChange}
                    {...props}
                >
                    {options.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>

                {label && (
                    <label
                        className={`absolute left-[14px] text-[var(--c3)] pointer-events-none transition-all duration-150
                        ${isLabelFloating
                            ? 'top-[-8px] translate-y-0 text-[0.78rem] bg-[var(--c1)] rounded-full px-[6px]'
                            : 'top-1/2 -translate-y-1/2 text-base text-[var(--c4)] bg-transparent px-[2px]'
                        }`}
                    >
                        {label}
                    </label>
                )}

                {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
            </div>
        )
    }
)

Select.displayName = 'Select'
export default Select
