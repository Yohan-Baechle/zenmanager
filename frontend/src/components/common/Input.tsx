import type { InputHTMLAttributes } from 'react'
import { forwardRef, useState } from 'react'
import React from 'react'
import { VisibilityIcon } from '../../assets/icons/visibility'
import { VisibilityOffIcon } from '../../assets/icons/visibility-off'

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
    label?: string
    type?: string
    icon?: React.ComponentType<React.SVGProps<SVGSVGElement>>
    visible?: boolean
    error?: string
    floatingLabel?: boolean
}

const Input = forwardRef<HTMLInputElement, InputProps>(
    ({ label, type, icon: Icon, visible, error, floatingLabel = false, ...props }, ref) => {
        const [showPassword, setShowPassword] = useState(false)
        const [isFocused, setIsFocused] = useState(false)
        const [hasValue, setHasValue] = useState(false)

        const inputType = visible && showPassword ? 'text' : type

        React.useEffect(() => {
            if (props.value || props.defaultValue) {
                setHasValue(true)
            }
        }, [props.value, props.defaultValue])

        const handleToggle = () => {
            setShowPassword(!showPassword)
        }

        const handleFocus = () => {
            setIsFocused(true)
        }

        const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
            setIsFocused(false)
            setHasValue(e.target.value.length > 0)
            props.onBlur?.(e)
        }

        const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
            setHasValue(e.target.value.length > 0)
            props.onChange?.(e)
        }

        const isLabelFloating = floatingLabel || isFocused || hasValue

        return (
            <div className="w-full relative">
                <input
                    ref={ref}
                    type={inputType}
                    className={`peer w-full border border-[var(--c3)] bg-[var(--c1)] text-[var(--c5)] rounded-[14px] py-[14px] outline-none text-base transition-[border-color,outline-color,transform] duration-150
                               active:translate-y-px active:duration-75 focus:border-[var(--c4)] focus:border-[3px] ${Icon ? 'px-[44px]' : visible ? 'pl-[14px] pr-[44px]' : 'px-[14px]'}`}
                    onFocus={handleFocus}
                    onBlur={handleBlur}
                    onChange={handleChange}
                    {...props}
                />
                {label && (
                    <label
                        className={`absolute ${Icon ? 'left-[44px]' : 'left-[14px]'} text-[var(--c3)] pointer-events-none transition-all duration-150
                                    ${isLabelFloating
                            ? 'top-[-8px] translate-y-0 text-[0.78rem] bg-[var(--c1)] rounded-full px-[6px]'
                            : 'top-1/2 -translate-y-1/2 text-base text-[var(--c4)] bg-transparent px-[2px]'
                        }`}
                    >
                        {label}
                    </label>
                )}
                {Icon && (
                    <Icon
                        className="absolute left-[14px] top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--c4)] pointer-events-none"
                        aria-hidden="true"
                    />
                )}
                {visible === true && (
                    <button
                        type="button"
                        onClick={handleToggle}
                        className="absolute right-[14px] top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--c4)] flex items-center justify-center cursor-pointer"
                        aria-label="Afficher le mot de passe"
                        aria-pressed={showPassword}
                    >
                        {showPassword ? (
                            <VisibilityOffIcon className="h-5 w-5" aria-hidden="true" />
                        ) : (
                            <VisibilityIcon className="h-5 w-5" aria-hidden="true" />
                        )}
                    </button>
                )}
                {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
            </div>
        )
    }
)

Input.displayName = 'Input'

export default Input
