# Guide de test des Voters et permissions

## Préparation

### 1. Charger les fixtures
```bash
cd C:\Users\banck\Projects\timemanager\T-DEV-700-project-NCY_1\backend
php bin/console doctrine:fixtures:load
```

### 2. Comptes de test créés

| Username | Password | Rôle | Équipe |
|----------|----------|------|--------|
| `admin` | `admin123` | ROLE_ADMIN | - |
| `manager_dev` | `password` | ROLE_MANAGER | Development Team |
| `employee_dev1` | `password` | ROLE_USER | Development Team |
| `employee_dev2` | `password` | ROLE_USER | Development Team |
| `manager_marketing` | `password` | ROLE_MANAGER | Marketing Team |
| `employee_marketing` | `password` | ROLE_USER | Marketing Team |

---

## Scénarios de test

### A. Test ROLE_ADMIN (Accès total)

#### 1. Login
```bash
curl -X POST http://localhost:8000/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

**Résultat attendu :** `{ "token": "eyJ0eXAi..." }`

**Variables :**
```bash
ADMIN_TOKEN="<copier_le_token_ici>"
```

#### 2. L'admin peut voir tous les utilisateurs
```bash
curl http://localhost:8000/api/users \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```
**✅ Attendu :** Liste de tous les users

#### 3. L'admin peut modifier n'importe quel user
```bash
curl -X PUT http://localhost:8000/api/users/3 \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"firstName":"Modified"}'
```
**✅ Attendu :** User modifié (même si pas dans son équipe)

#### 4. L'admin peut supprimer n'importe quelle team
```bash
curl -X DELETE http://localhost:8000/api/teams/2 \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```
**✅ Attendu :** 204 No Content

---

### B. Test ROLE_MANAGER (Accès à son équipe uniquement)

#### 1. Login manager_dev
```bash
curl -X POST http://localhost:8000/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"username":"manager_dev","password":"password"}'
```

**Variables :**
```bash
MANAGER_DEV_TOKEN="<copier_le_token_ici>"
```

#### 2. Le manager peut voir les users de son équipe
```bash
curl http://localhost:8000/api/users/3 \
  -H "Authorization: Bearer $MANAGER_DEV_TOKEN"
```
**✅ Attendu :** Détails de employee_dev1 (id=3)

#### 3. Le manager peut créer un clock pour un membre de son équipe
```bash
curl -X POST http://localhost:8000/api/clocks \
  -H "Authorization: Bearer $MANAGER_DEV_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "time": "2025-10-10T09:00:00+00:00",
    "status": true,
    "userId": 3
  }'
```
**✅ Attendu :** 201 Created

#### 4. Le manager NE PEUT PAS voir les clocks d'une autre équipe
```bash
# employee_marketing est dans l'équipe Marketing (id=6)
curl http://localhost:8000/api/users/6/clocks \
  -H "Authorization: Bearer $MANAGER_DEV_TOKEN"
```
**❌ Attendu :** 403 Forbidden

#### 5. Le manager peut modifier son équipe
```bash
curl -X PUT http://localhost:8000/api/teams/1 \
  -H "Authorization: Bearer $MANAGER_DEV_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Dev Team Updated"}'
```
**✅ Attendu :** Team modifiée

#### 6. Le manager NE PEUT PAS modifier une autre équipe
```bash
curl -X PUT http://localhost:8000/api/teams/2 \
  -H "Authorization: Bearer $MANAGER_DEV_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Marketing Updated"}'
```
**❌ Attendu :** 403 Forbidden

---

### C. Test ROLE_USER (Accès personnel uniquement)

#### 1. Login employee_dev1
```bash
curl -X POST http://localhost:8000/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"username":"employee_dev1","password":"password"}'
```

**Variables :**
```bash
EMPLOYEE_TOKEN="<copier_le_token_ici>"
```

#### 2. L'employé peut voir ses propres données
```bash
curl http://localhost:8000/api/users/3 \
  -H "Authorization: Bearer $EMPLOYEE_TOKEN"
```
**✅ Attendu :** Ses propres infos

#### 3. L'employé peut créer son propre clock
```bash
curl -X POST http://localhost:8000/api/clocks \
  -H "Authorization: Bearer $EMPLOYEE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "time": "2025-10-10T09:00:00+00:00",
    "status": true,
    "userId": 3
  }'
```
**✅ Attendu :** 201 Created

#### 4. L'employé NE PEUT PAS créer un clock pour quelqu'un d'autre
```bash
curl -X POST http://localhost:8000/api/clocks \
  -H "Authorization: Bearer $EMPLOYEE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "time": "2025-10-10T09:00:00+00:00",
    "status": true,
    "userId": 4
  }'
```
**❌ Attendu :** 403 Forbidden

#### 5. L'employé NE PEUT PAS voir les clocks d'autres users
```bash
curl http://localhost:8000/api/users/4/clocks \
  -H "Authorization: Bearer $EMPLOYEE_TOKEN"
```
**❌ Attendu :** 403 Forbidden

#### 6. L'employé NE PEUT PAS modifier un autre user
```bash
curl -X PUT http://localhost:8000/api/users/4 \
  -H "Authorization: Bearer $EMPLOYEE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"firstName":"Hacked"}'
```
**❌ Attendu :** 403 Forbidden

---

## Tests WorkingTime

### Manager peut modifier les workingTimes de son équipe
```bash
# 1. Créer un workingTime pour employee_dev1 (id=3)
curl -X POST http://localhost:8000/api/workingtimes/3 \
  -H "Authorization: Bearer $MANAGER_DEV_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "startTime": "2025-10-10T09:00:00+00:00",
    "endTime": "2025-10-10T17:00:00+00:00"
  }'
```
**✅ Attendu :** 201 Created

```bash
# 2. Le modifier (avec l'ID du workingTime créé)
curl -X PUT http://localhost:8000/api/workingtimes/1 \
  -H "Authorization: Bearer $MANAGER_DEV_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "startTime": "2025-10-10T08:30:00+00:00",
    "endTime": "2025-10-10T17:30:00+00:00"
  }'
```
**✅ Attendu :** WorkingTime modifié

---

## Tests Clock (immuable)

### Vérifier que Clock ne peut PAS être modifié/supprimé

```bash
# 1. Créer un clock
curl -X POST http://localhost:8000/api/clocks \
  -H "Authorization: Bearer $EMPLOYEE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "time": "2025-10-10T09:00:00+00:00",
    "status": true,
    "userId": 3
  }'
```

```bash
# 2. Essayer de le modifier (devrait échouer - endpoint n'existe plus)
curl -X PUT http://localhost:8000/api/clocks/1 \
  -H "Authorization: Bearer $EMPLOYEE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "time": "2025-10-10T10:00:00+00:00"
  }'
```
**❌ Attendu :** 404 Not Found (route n'existe pas)

```bash
# 3. Essayer de le supprimer (devrait échouer - endpoint n'existe plus)
curl -X DELETE http://localhost:8000/api/clocks/1 \
  -H "Authorization: Bearer $EMPLOYEE_TOKEN"
```
**❌ Attendu :** 404 Not Found (route n'existe pas)

---

## Checklist de validation

### ROLE_ADMIN
- [ ] ✅ Peut voir tous les users
- [ ] ✅ Peut modifier n'importe quel user
- [ ] ✅ Peut supprimer n'importe quel user (sauf lui-même)
- [ ] ✅ Peut modifier/supprimer n'importe quelle team
- [ ] ✅ Peut voir/modifier tous les clocks et workingTimes

### ROLE_MANAGER
- [ ] ✅ Peut voir les users de son équipe
- [ ] ✅ Peut modifier les users de son équipe
- [ ] ✅ Peut créer des clocks pour son équipe
- [ ] ✅ Peut voir/modifier les workingTimes de son équipe
- [ ] ❌ Ne peut PAS voir/modifier les users d'autres équipes
- [ ] ❌ Ne peut PAS modifier les teams dont il n'est pas le manager

### ROLE_USER
- [ ] ✅ Peut voir ses propres données
- [ ] ✅ Peut modifier ses propres données
- [ ] ✅ Peut créer ses propres clocks
- [ ] ✅ Peut créer/modifier ses propres workingTimes
- [ ] ❌ Ne peut PAS se supprimer lui-même
- [ ] ❌ Ne peut PAS voir/modifier les données d'autres users
- [ ] ❌ Ne peut PAS créer de clocks pour d'autres users

### Clock (Immuabilité)
- [ ] ✅ Peut être créé
- [ ] ✅ Peut être visualisé
- [ ] ❌ Ne peut PAS être modifié
- [ ] ❌ Ne peut PAS être supprimé