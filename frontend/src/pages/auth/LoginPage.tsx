import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../../hooks/useAuth'
import Input from '../../components/common/Input'
import Button from '../../components/common/Button'
import Card from '../../components/common/Card'
import AccountCircleIcon from '../../assets/icons/AccountCircle.svg'
import PasswordIcon from '../../assets/icons/Password.svg'
import Plante1 from '../../assets/plante1.svg'
import Plante2 from '../../assets/plante2.svg'
import Logo from '../../assets/Logo.svg'

export default function LoginPage() {
    const [username, setUsername] = useState('')
    const [password, setPassword] = useState('')
    const [error, setError] = useState('')
    const [loading, setLoading] = useState(false)
    const { login } = useAuth()
    const navigate = useNavigate()

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault()
        setError('')
        setLoading(true)

        try {
            await login({ username, password })
            navigate('/dashboard')
        } catch (error) {
            setError(`Invalid username or password: ${error instanceof Error ? error.message : 'Unknown error'}`)
        } finally {
            setLoading(false)
        }
    }

    return (
        <div className="min-h-screen flex items-center justify-center p-4">
            <div className="w-full max-w-md">
                <img
                    className="fixed top-0 left-1/2 translate-x-[20%] lg:translate-x-[40%] -translate-y-[15%] -rotate-[30deg] w-[min(100%,600px)] h-auto opacity-[0.15] pointer-events-none select-none z-[-1]"
                    src={Plante1} alt="Decorative Plant"
                />
                <img
                    className="fixed bottom-0 right-1/2 -translate-x-[40%] lg:-translate-x-[60%] translate-y-[20%] rotate-[130deg] w-[min(100%,600px)] h-auto opacity-[0.15] pointer-events-none select-none z-[-1]"
                    src={Plante2} alt="Decorative Plant"
                />
                <Card
                    title="Connexion"
                    icon={Logo}
                    description="Accéder à votre tableau de bord."
                    info="Vous n'avez pas de compte ? <span class='underline'>Contactez l'administrateur</span>."
                >
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <Input
                            label="Nom d'utilisateur"
                            icon={AccountCircleIcon}
                            type="text"
                            value={username}
                            onChange={(e) => setUsername(e.target.value)}
                            required
                        />
                        <Input
                            label="Mot de passe"
                            icon={PasswordIcon}
                            type="password"
                            visible={true}
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                        />
                        {error && <p className="text-sm text-red-600">{error}</p>}
                        <hr className="border-[var(--c2)] mx-2 my-6" />
                        <Button type="submit" disabled={loading} className="w-full">
                            {loading ? 'Connexion...' : 'Se connecter'}
                        </Button>
                    </form>
                </Card>
            </div>
        </div>
    )
}
