import { createContext } from 'react'

export type SidebarState = 'open' | 'semi' | 'closed'

export interface SidebarContextType {
    sidebarState: SidebarState
    setSidebarState: (state: SidebarState) => void
    toggleSidebar: () => void
}

export const SidebarContext = createContext<SidebarContextType | undefined>(undefined)
