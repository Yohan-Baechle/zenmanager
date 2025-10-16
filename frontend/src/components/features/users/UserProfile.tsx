import type {User} from '../../../types/user.types'
import SectionDivider from "../../common/SectionDivider.tsx";
import Button from "../../common/Button.tsx";
import {AccountCircleIcon} from "../../../assets/icons/account-circle.tsx";
import {SupervisorAccountIcon} from "../../../assets/icons/supervisor-account.tsx";
import {CalendarTodayIcon} from "../../../assets/icons/calendar-today.tsx";
import {AdminPanelSettingsIcon} from "../../../assets/icons/admin-panel-settings.tsx";
import {AlternateEmailIcon} from "../../../assets/icons/alternate-email.tsx";
import {IdCardIcon} from "../../../assets/icons/id-card.tsx";
import {PhoneInTalkIcon} from "../../../assets/icons/phone-in-talk.tsx";
import {usersApi} from "../../../api/users.api.ts";
import {useState} from "react";

interface UserProfileProps {
    data: User
}

export default function UserProfile({ data }: UserProfileProps) {
    const [loading, setLoading] = useState(false)

    const handleRegeneratePassword = async () => {
        if (!confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe ?')) {
            return
        }

        setLoading(true)
        try {
            await usersApi.regeneratePassword(data.id)
            alert('Le mot de passe a été réinitialisé avec succès. Un nouveau mot de passe a été envoyé par e-mail.')
        } catch (error) {
            alert(`Erreur lors de la réinitialisation : ${error instanceof Error ? error.message : 'Erreur inconnue'}`)
        } finally {
            setLoading(false)
        }
    }

    return (
        <div className="">
            <div className="grid grid-cols-1 sm:grid-cols-[max-content_1fr] gap-0 items-center text-[var(--c5)]">
                <div className="pl-5 pr-10 font-semibold flex items-center gap-2">
                    <AccountCircleIcon className="w-5 h-5"/>
                    <label>Prénom</label>
                </div>
                <p className="">{data.firstName}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <div className="pl-5 pr-10 font-semibold flex items-center gap-2">
                    <AccountCircleIcon className="w-5 h-5"/>
                    <label>Nom</label>
                </div>
                <p className="">{data.lastName}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <div className="pl-5 pr-10 font-semibold flex items-center gap-2">
                    <IdCardIcon className="w-5 h-5"/>
                    <label>Identifiant</label>
                </div>
                <p className="">{data.username}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <div className="pl-5 pr-10 font-semibold flex items-center gap-2">
                    <AlternateEmailIcon className="w-5 h-5"/>
                    <label>Adresse e-mail</label>
                </div>
                <p className="">{data.email}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <div className="pl-5 pr-10 font-semibold flex items-center gap-2">
                    <PhoneInTalkIcon className="w-5 h-5"/>
                    <label>Numéro de téléphone</label>
                </div>
                <p className="">{data.phoneNumber}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <div className="pl-5 pr-10 font-semibold flex items-center gap-2">
                    <AdminPanelSettingsIcon className="w-5 h-5"/>
                    <label>Rôle</label>
                </div>
                <p className="">{data.role}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <div className="pl-5 pr-10 font-semibold flex items-center gap-2">
                    <SupervisorAccountIcon className="w-5 h-5"/>
                    <label>Membre de l'équipe</label>
                </div>
                <p className="">{data.teams ? data.teams.name : 'Aucune'}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <div className="pl-5 pr-10 font-semibold flex items-center gap-2">
                    <CalendarTodayIcon className="w-5 h-5"/>
                    <label>Compte créé le</label>
                </div>
                <p className="">{data.createdAt ? new Date(data.createdAt).toLocaleDateString() : 'N/A'}</p>
            </div>
            <SectionDivider/>
            <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <a className="underline cursor-pointer px-5 text-[var(--c4)] hover:text-[var(--c5)]"
                   onClick={() => alert('Fonctionnalité à venir')}
                >Modifier le profil</a>
                <Button onClick={handleRegeneratePassword} disabled={loading}>
                    {loading ? 'Réinitialisation...' : 'Réinitialiser le mot de passe'}
                </Button>
            </div>
        </div>
    )
}
