import { useState, useEffect, useCallback } from 'react'

const KONAMI_CODE = [
    'ArrowUp',
    'ArrowUp',
    'ArrowDown',
    'ArrowDown',
    'ArrowLeft',
    'ArrowRight',
    'ArrowLeft',
    'ArrowRight',
    'b',
    'a'
]

export function useKonamiCode(): [boolean, () => void] {
    const [konamiActivated, setKonamiActivated] = useState(false)
    const [inputSequence, setInputSequence] = useState<string[]>([])

    const handleKeyDown = useCallback((event: KeyboardEvent) => {
        const key = event.key.length === 1 ? event.key.toLowerCase() : event.key
        const newSequence = [...inputSequence, key].slice(-KONAMI_CODE.length)
        setInputSequence(newSequence)

        if (newSequence.length === KONAMI_CODE.length &&
            newSequence.every((key, index) => key === KONAMI_CODE[index])) {
            setKonamiActivated(true)
        }
    }, [inputSequence])

    const reset = useCallback(() => {
        setKonamiActivated(false)
        setInputSequence([])
    }, [])

    useEffect(() => {
        window.addEventListener('keydown', handleKeyDown)
        return () => window.removeEventListener('keydown', handleKeyDown)
    }, [handleKeyDown])

    return [konamiActivated, reset]
}
