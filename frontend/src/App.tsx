import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider } from './context/AuthProvider'
import Layout from './components/layout/Layout'
import ProtectedRoute from './components/layout/ProtectedRoute'
import LoginPage from './pages/auth/LoginPage'
import UsersPage from './pages/users/UsersPage'
import CreateUserPage from './pages/users/CreateUserPage'
import EditUserPage from './pages/users/EditUserPage'
import ProfilePage from './pages/users/ProfilePage'
import TeamsPage from './pages/teams/TeamsPage'
import CreateTeamPage from './pages/teams/CreateTeamPage'
import EditTeamPage from './pages/teams/EditTeamPage'
import ClockPage from './pages/clocks/ClockPage'
import EmployeeDashboard from './pages/dashboard/EmployeeDashboard'
import ManagerDashboard from './pages/dashboard/ManagerDashboard'
import EmployeeDetailDashboard from './pages/dashboard/EmployeeDetailDashboard'
import ReportsPage from './pages/reports/ReportsPage'
import AdminPage from './pages/admin/AdminPage'

function App() {
    return (
        <AuthProvider>
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

                        <Route path="/users" element={
                            <ProtectedRoute requiredRole={['manager']}>
                                <UsersPage />
                            </ProtectedRoute>
                        } />

                        <Route path="/users/create" element={
                            <ProtectedRoute requiredRole={['manager']}>
                                <CreateUserPage />
                            </ProtectedRoute>
                        } />

                        <Route path="/users/edit/:id" element={
                            <ProtectedRoute requiredRole={['manager']}>
                                <EditUserPage />
                            </ProtectedRoute>
                        } />

                        <Route path="/profile" element={
                            <ProtectedRoute>
                                <ProfilePage />
                            </ProtectedRoute>
                        } />

                        <Route path="/teams" element={
                            <ProtectedRoute requiredRole={['manager']}>
                                <TeamsPage />
                            </ProtectedRoute>
                        } />

                        <Route path="/teams/create" element={
                            <ProtectedRoute requiredRole={['manager']}>
                                <CreateTeamPage />
                            </ProtectedRoute>
                        } />

                        <Route path="/teams/edit/:id" element={
                            <ProtectedRoute requiredRole={['manager']}>
                                <EditTeamPage />
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
