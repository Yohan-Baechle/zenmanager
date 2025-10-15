import { Outlet } from 'react-router-dom'
import Header from './Header'
import Sidebar from './Sidebar'

export default function Layout() {
    return (
        <div className="min-h-screen">
            <Header />
            <div className="flex">
                <Sidebar />
                <main className="flex-1 p-8 overflow-y-scroll h-[calc(100vh-72px)]">
                    <Outlet />
                </main>
            </div>
        </div>
    )
}
