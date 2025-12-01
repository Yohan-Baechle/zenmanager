import type { InputHTMLAttributes } from 'react'
import { forwardRef } from 'react'

interface CheckboxProps extends Omit<InputHTMLAttributes<HTMLInputElement>, 'type'> {
    label?: string
    error?: string
}

const Checkbox = forwardRef<HTMLInputElement, CheckboxProps>(
    ({ label, error, ...props }, ref) => {
        return (
            <div className="w-full relative">
                <label className="flex items-center gap-3 cursor-pointer group">
                    <div className="relative flex items-center justify-center">
                        <input
                            ref={ref}
                            type="checkbox"
                            className="peer appearance-none w-5 h-5 border-2 border-[var(--c3)] bg-[var(--c1)] rounded cursor-pointer
                                     transition-all duration-150 checked:bg-[var(--c4)] checked:border-[var(--c4)]
                                     focus:outline-none focus:ring-2 focus:ring-[var(--c4)] focus:ring-offset-2 focus:ring-offset-[var(--c1)]
                                     hover:border-[var(--c4)] active:scale-95"
                            {...props}
                        />
                        <svg
                            className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity duration-150"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"
                        ><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7"/></svg>
                    </div>
                    {label && (
                        <span className="text-[var(--c5)] text-base select-none group-hover:text-[var(--c4)] transition-colors duration-150">
                            {label}
                        </span>
                    )}
                </label>
                {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
            </div>
        )
    }
)

Checkbox.displayName = 'Checkbox'

export default Checkbox
