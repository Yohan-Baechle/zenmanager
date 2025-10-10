import type { InputHTMLAttributes } from 'react'
import { forwardRef, useState } from 'react'
import VisibilityIcon from '../../assets/icons/Visibility.svg'
import VisibilityOffIcon from '../../assets/icons/VisibilityOff.svg'

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
    label?: string
    type?: string
    icon?: string
    visible?: boolean
    error?: string
}

const Input = forwardRef<HTMLInputElement, InputProps>(
    ({ label, type, icon, visible, error, ...props }, ref) => {
        const [showPassword, setShowPassword] = useState(false)
        const [isFocused, setIsFocused] = useState(false)
        const [hasValue, setHasValue] = useState(false)

        const inputType = visible && showPassword ? 'text' : type

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

        const isLabelFloating = isFocused || hasValue

        return (
            <div className="w-full relative">
                <input
                    ref={ref}
                    type={inputType}
                    className="peer w-full border border-[var(--c3)] bg-[var(--c1)] text-[var(--c5)] rounded-[14px] py-[14px] px-[44px] outline-none text-base transition-[border-color,outline-color,transform] duration-150
                               active:translate-y-px active:duration-75 focus:border-[var(--c4)] focus:border-[3px]"
                    onFocus={handleFocus}
                    onBlur={handleBlur}
                    onChange={handleChange}
                    {...props}
                />
                {label && (
                    <label
                        className={`absolute left-[44px] text-[var(--c3)] pointer-events-none transition-all duration-150
                                    ${isLabelFloating
                            ? 'top-[-8px] translate-y-0 text-[0.78rem] bg-[var(--c1)] rounded-full px-[6px]'
                            : 'top-1/2 -translate-y-1/2 text-base text-[var(--c4)] bg-transparent px-[2px]'
                        }`}
                    >
                        {label}
                    </label>
                )}
                <img
                    className="absolute left-[14px] top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--c4)] pointer-events-none"
                    src={icon} alt="User Icon"
                />
                {visible === true && (
                    <button
                        type="button"
                        onClick={handleToggle}
                        className="absolute right-[14px] top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--c4)] flex items-center justify-center cursor-pointer"
                        aria-label="Afficher le mot de passe"
                        aria-pressed={showPassword}
                    >
                        <img
                            src={showPassword ? VisibilityOffIcon : VisibilityIcon}
                            className="h-5 w-5"
                            aria-hidden="true"
                            alt=""
                        />
                    </button>
                )}
                {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
            </div>
        )
    }
)

Input.displayName = 'Input'

export default Input
