import { useState, type ReactNode } from 'react'
import { SidebarContext, type SidebarState } from './SidebarContext'

export function SidebarProvider({ children }: { children: ReactNode }) {
    const [sidebarState, setSidebarState] = useState<SidebarState>('open')

    const toggleSidebar = () => {
        setSidebarState(prev => {
            if (prev === 'open') return 'semi'
            if (prev === 'semi') return 'closed'
            return 'open'
        })
    }

    return (
        <SidebarContext.Provider value={{ sidebarState, setSidebarState, toggleSidebar }}>
            {children}
        </SidebarContext.Provider>
    )
}
