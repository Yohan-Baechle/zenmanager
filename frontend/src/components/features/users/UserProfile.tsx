import type {User} from '../../../types/user.types'
import SectionDivider from "../../common/SectionDivider.tsx";
import Button from "../../common/Button.tsx";

interface UserProfileProps {
    data: User
}

export default function UserProfile({ data }: UserProfileProps) {
    return (
        <div className="">
            <div className="grid grid-cols-1 sm:grid-cols-[max-content_1fr] gap-0 items-center text-[var(--c5)]">
                <label className="pl-5 pr-10 font-semibold">Prénom</label>
                <p className="">{data.firstName}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <label className="pl-5 pr-10 font-semibold">Nom</label>
                <p className="">{data.lastName}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <label className="pl-5 pr-10 font-semibold">Identifiant</label>
                <p className="">{data.username}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <label className="pl-5 pr-10 font-semibold">Adresse e-mail</label>
                <p className="">{data.email}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <label className="pl-5 pr-10 font-semibold">Numéro de téléphone</label>
                <p className="">{data.phoneNumber}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <label className="pl-5 pr-10 font-semibold">Rôle</label>
                <p className="">{data.role}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <label className="pl-5 pr-10 font-semibold">Membre de l'équipe</label>
                <p className="">{data.teams ? data.teams.name : 'Aucune'}</p>
                <div className="sm:col-span-2"><SectionDivider/></div>
                <label className="pl-5 pr-10 font-semibold">Compte créé le</label>
                <p className="">{data.createdAt ? new Date(data.createdAt).toLocaleDateString() : 'N/A'}</p>
            </div>
            <SectionDivider/>
            <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <a className="underline cursor-pointer px-5 text-[var(--c4)] hover:text-[var(--c5)]"
                   onClick={() => alert('Fonctionnalité à venir')}
                >Modifier le profil</a>
                <Button onClick={() => alert('Fonctionnalité à venir')}>
                    Réinitialiser le mot de passe
                </Button>
            </div>
        </div>
    )
}
