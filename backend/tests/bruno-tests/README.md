# Collection Bruno - Tests TimeManager

## 🚀 Installation

1. **Installer Bruno** : https://www.usebruno.com/downloads

2. **Ouvrir la collection** :
   - Lancez Bruno
   - Cliquez sur "Open Collection"
   - Sélectionnez le dossier `bruno-tests`

3. **Charger les fixtures** :
```bash
cd C:\Users\banck\Projects\timemanager\T-DEV-700-project-NCY_1\backend
php bin/console doctrine:fixtures:load
```

## 📋 Comment utiliser

### Étape 1 : Authentification
Exécutez dans l'ordre :
1. **Authentication → Login Admin** ✅ Récupère le token admin
2. **Authentication → Login Manager Dev** ✅ Récupère le token manager
3. **Authentication → Login Employee Dev** ✅ Récupère le token employee

Les tokens sont automatiquement sauvegardés dans les variables d'environnement.

### Étape 2 : Tests ADMIN
Dossier **ADMIN Tests** - Vérifiez que l'admin :
- ✅ Peut voir tous les users
- ✅ Peut modifier n'importe quel user
- ✅ Peut supprimer n'importe quelle team

### Étape 3 : Tests MANAGER
Dossier **MANAGER Tests** - Vérifiez que le manager :
- ✅ Peut voir les membres de son équipe
- ✅ Peut créer des clocks pour son équipe
- ❌ Ne peut PAS voir les clocks d'une autre équipe
- ✅ Peut modifier sa propre team
- ❌ Ne peut PAS modifier une autre team

### Étape 4 : Tests EMPLOYEE
Dossier **EMPLOYEE Tests** - Vérifiez que l'employé :
- ✅ Peut voir ses propres données
- ✅ Peut créer son propre clock
- ❌ Ne peut PAS créer de clock pour quelqu'un d'autre
- ❌ Ne peut PAS voir les clocks d'autres users
- ❌ Ne peut PAS modifier d'autres users

### Étape 5 : Tests Immuabilité Clock
Dossier **Clock Immutability** - Vérifiez que :
- ❌ Les clocks ne peuvent PAS être modifiés (404)
- ❌ Les clocks ne peuvent PAS être supprimés (404)

## 🎯 Structure de la collection

```
bruno-tests/
├── environments/
│   └── Local.bru              # Variables d'environnement
├── Authentication/
│   ├── Login Admin.bru
│   ├── Login Manager Dev.bru
│   └── Login Employee Dev.bru
├── ADMIN Tests/
│   ├── Admin can view all users.bru
│   ├── Admin can modify any user.bru
│   └── Admin can delete any team.bru
├── MANAGER Tests/
│   ├── Manager can view team member.bru
│   ├── Manager can create clock for team member.bru
│   ├── Manager CANNOT view other team clocks.bru
│   ├── Manager can modify own team.bru
│   └── Manager CANNOT modify other team.bru
├── EMPLOYEE Tests/
│   ├── Employee can view own data.bru
│   ├── Employee can create own clock.bru
│   ├── Employee CANNOT create clock for others.bru
│   ├── Employee CANNOT view others clocks.bru
│   └── Employee CANNOT modify other user.bru
└── Clock Immutability/
    ├── Cannot UPDATE clock.bru
    └── Cannot DELETE clock.bru
```

## ✅ Comptes de test

| Username | Password | Rôle | Équipe |
|----------|----------|------|--------|
| `admin` | `admin123` | ROLE_ADMIN | - |
| `manager_dev` | `password` | ROLE_MANAGER | Development Team |
| `employee_dev1` | `password` | ROLE_USER | Development Team |
| `employee_dev2` | `password` | ROLE_USER | Development Team |
| `manager_marketing` | `password` | ROLE_MANAGER | Marketing Team |
| `employee_marketing` | `password` | ROLE_USER | Marketing Team |

## 💡 Astuces Bruno

- **Run Collection** : Clic droit sur un dossier → "Run Folder" pour exécuter tous les tests
- **Tests automatiques** : Les assertions sont incluses dans chaque requête
- **Variables** : Les tokens sont auto-sauvegardés après les logins
- **Git-friendly** : Tous les fichiers sont en texte brut, parfait pour Git

Bon testing ! 🎉
