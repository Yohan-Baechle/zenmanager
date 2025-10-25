import { Outlet } from 'react-router-dom'
import { SidebarProvider } from '../../context/SidebarProvider'
import Header from './Header'
import Sidebar from './Sidebar'

export default function Layout() {
    return (
        <SidebarProvider>
            <div className="min-h-screen">
                <Header />
                <div className="flex">
                    <Sidebar />
                    <main className="flex-1 p-4 md:p-8 overflow-y-scroll h-[calc(100vh-72px)]">
                        <Outlet />
                    </main>
                </div>
            </div>
        </SidebarProvider>
    )
}
