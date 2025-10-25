import type { TextareaHTMLAttributes } from 'react'
import { forwardRef, useEffect, useRef, useState } from 'react'

interface TextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    label?: string
    error?: string
}

const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(
    ({ label, error, className, ...props }, ref) => {
        const innerRef = useRef<HTMLTextAreaElement | null>(null)
        const combinedRef = (node: HTMLTextAreaElement | null) => {
            innerRef.current = node
            if (typeof ref === 'function') ref(node)
            else if (ref) (ref as React.MutableRefObject<HTMLTextAreaElement | null>).current = node
        }

        const [isFocused, setIsFocused] = useState(false)
        const [hasValue, setHasValue] = useState(!!props.value || !!props.defaultValue)

        useEffect(() => {
            if (innerRef.current && innerRef.current.value.length > 0) {
                setHasValue(true)
            }
        }, [])

        useEffect(() => {
            if (props.value !== undefined) {
                const v = typeof props.value === 'string' ? props.value : String(props.value)
                setHasValue(v.length > 0)
            }
        }, [props.value])

        const handleFocus = () => setIsFocused(true)

        const handleBlur = (e: React.FocusEvent<HTMLTextAreaElement>) => {
            setIsFocused(false)
            setHasValue(e.target.value.length > 0)
            props.onBlur?.(e)
        }

        const handleChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
            setHasValue(e.target.value.length > 0)
            props.onChange?.(e)
        }

        const isLabelFloating = isFocused || hasValue

        return (
            <div className="w-full relative">
        <textarea
            ref={combinedRef}
            className={`peer w-full border border-[var(--c3)] bg-[var(--c1)] text-[var(--c5)] rounded-[14px] py-[14px] px-[20px] outline-none text-base transition-[border-color,outline-color] duration-150
                     focus:border-[var(--c4)] focus:border-[3px] resize-none ${className || ''}`}
            onFocus={handleFocus}
            onBlur={handleBlur}
            onChange={handleChange}
            {...props}
        />
                {label && (
                    <label
                        className={`absolute left-[20px] text-[var(--c3)] pointer-events-none transition-all duration-150
                        ${isLabelFloating
                            ? 'top-[-8px] translate-y-0 text-[0.78rem] bg-[var(--c1)] rounded-full px-[6px]'
                            : 'top-[14px] translate-y-0 text-base text-[var(--c4)] bg-transparent px-[2px]'
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

Textarea.displayName = 'Textarea'

export default Textarea
