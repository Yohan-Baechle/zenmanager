import { useState, type ReactNode, useEffect } from 'react'
import { SidebarContext, type SidebarState } from './SidebarContext'
import { useMediaQuery } from '../hooks/useMediaQuery'

export function SidebarProvider({ children }: { children: ReactNode }) {
    const deviceType = useMediaQuery()
    const [sidebarState, setSidebarState] = useState<SidebarState>(() => {
        if (typeof window !== 'undefined' && window.innerWidth < 768) {
            return 'closed'
        }
        return 'open'
    })

    useEffect(() => {
        if (deviceType === 'mobile' && sidebarState === 'semi') {
            setSidebarState('closed')
        } else if (deviceType === 'desktop' && sidebarState === 'closed') {
            setSidebarState('open')
        }
    }, [deviceType])

    const toggleSidebar = () => {
        setSidebarState(prev => {
            if (deviceType === 'desktop') {
                return prev === 'open' ? 'semi' : 'open'
            } else if (deviceType === 'tablet') {
                return prev === 'open' ? 'semi' : 'open'
            } else {
                return prev === 'open' ? 'closed' : 'open'
            }
        })
    }

    return (
        <SidebarContext.Provider value={{ sidebarState, setSidebarState, toggleSidebar }}>
            {children}
        </SidebarContext.Provider>
    )
}
