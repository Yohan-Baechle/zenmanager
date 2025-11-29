import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'sonner'
import { AuthProvider } from './context/AuthProvider'
import Layout from './components/layout/Layout'
import ProtectedRoute from './components/layout/ProtectedRoute'
import LoginPage from './pages/auth/LoginPage'
import ProfilePage from './pages/users/ProfilePage'
import ClockPage from './pages/clocks/ClockPage'
import EmployeeDashboard from './pages/dashboard/EmployeeDashboard'
import ManagerDashboard from './pages/dashboard/ManagerDashboard'
import EmployeeDetailDashboard from './pages/dashboard/EmployeeDetailDashboard'
import ReportsPage from './pages/reports/ReportsPage'
import AdminPage from './pages/admin/AdminPage'
import ClockRequestsManagementPage from './pages/clocks/ClockRequestsManagementPage'

function App() {
    return (
        <AuthProvider>
            <Toaster />
            <Router>
                <Routes>
                    <Route path="/login" element={<LoginPage />} />

                    <Route element={<Layout />}>
                        <Route path="/" element={<Navigate to="/dashboard" replace />} />

                        <Route path="/dashboard" element={
                            <ProtectedRoute>
                                <EmployeeDashboard />
                            </ProtectedRoute>
                        } />

                        <Route path="/manager/dashboard" element={
                            <ProtectedRoute requiredRole={['manager']}>
                                <ManagerDashboard />
                            </ProtectedRoute>
                        } />

                        <Route path="/manager/employee/:id" element={
                            <ProtectedRoute requiredRole={['manager']}>
                                <EmployeeDetailDashboard />
                            </ProtectedRoute>
                        } />

                        <Route path="/clock" element={
                            <ProtectedRoute>
                                <ClockPage />
                            </ProtectedRoute>
                        } />

                        <Route path="/profile" element={
                            <ProtectedRoute>
                                <ProfilePage />
                            </ProtectedRoute>
                        } />

                        <Route path="/clock-requests" element={
                            <ProtectedRoute requiredRole={['manager', 'admin']}>
                                <ClockRequestsManagementPage />
                            </ProtectedRoute>
                        } />

                        <Route path="/reports" element={
                            <ProtectedRoute requiredRole={['admin', 'manager']}>
                                <ReportsPage />
                            </ProtectedRoute>
                        } />

                        <Route path="/admin" element={
                            <ProtectedRoute requiredRole={['admin']}>
                                <AdminPage />
                            </ProtectedRoute>
                        } />
                    </Route>
                </Routes>
            </Router>
        </AuthProvider>
    )
}

export default App
