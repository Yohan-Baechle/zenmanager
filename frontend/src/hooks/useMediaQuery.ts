import { useState, useEffect } from 'react'

export type DeviceType = 'desktop' | 'tablet' | 'mobile'

export function useMediaQuery(): DeviceType {
    const [deviceType, setDeviceType] = useState<DeviceType>(() => {
        if (typeof window === 'undefined') return 'desktop'
        if (window.innerWidth < 768) return 'mobile'
        if (window.innerWidth < 1024) return 'tablet'
        return 'desktop'
    })

    useEffect(() => {
        const handleResize = () => {
            if (window.innerWidth < 768) {
                setDeviceType('mobile')
            } else if (window.innerWidth < 1024) {
                setDeviceType('tablet')
            } else {
                setDeviceType('desktop')
            }
        }

        window.addEventListener('resize', handleResize)
        return () => window.removeEventListener('resize', handleResize)
    }, [])

    return deviceType
}
