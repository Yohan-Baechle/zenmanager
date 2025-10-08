# Time Manager

Time management application built with Symfony (backend) and React (frontend).

## 📋 Prerequisites

- Docker and Docker Compose
- Git

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd T-DEV-700-project-NCY_1
   ```

2. **Environment variables configuration**

   Create a `.env` file at the project root:
   ```env
   POSTGRES_DB=your_database_name
   POSTGRES_USER=your_database_user
   POSTGRES_PASSWORD=your_database_password
   ```

   Create a `backend/.env` file:
   ```env
   DATABASE_URL="postgresql://db_user:db_password@database:5432/db_name?serverVersion=18&charset=utf8"
   APP_ENV=dev
   APP_SECRET=your_app_secret_key_here
   JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
   JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
   JWT_PASSPHRASE=your_jwt_passphrase_here
   ```

3. **Generate JWT keys**
   ```bash
   mkdir -p backend/config/jwt
   openssl genpkey -out backend/config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
   openssl pkey -in backend/config/jwt/private.pem -out backend/config/jwt/public.pem -pubout
   ```

4. **Start the application**
   ```bash
   docker compose up -d
   ```

5. **Initialize the database**
   ```bash
   docker compose exec backend php bin/console doctrine:migrations:migrate
   docker compose exec backend php bin/console doctrine:fixtures:load
   ```

## 🌐 Access to services

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8080
- **Adminer** (database management): http://localhost:8081
  - System: PostgreSQL
  - Server: database
  - Username: (your configured username)
  - Password: (your configured password)
  - Database: (your configured database name)

## 🏗️ Architecture

### Backend (Symfony 7.3)
- **Framework**: Symfony 7.3 with PHP 8.2+
- **Database**: PostgreSQL 18
- **ORM**: Doctrine
- **Authentication**: JWT (LexikJWTAuthenticationBundle)
- **API**: RESTful with serialization groups

#### Structure
```
backend/
├── src/
│   ├── Controller/Api/
│   │   ├── UserController.php
│   │   └── AuthController.php
│   ├── Entity/
│   │   └── User.php
│   ├── Repository/
│   │   └── UserRepository.php
│   └── DataFixtures/
│       └── UserFixtures.php
└── config/
```

#### Available entities
- **User**: User management with roles, authentication and timestamps

### Frontend (React + TypeScript)
- **Framework**: React 19 with TypeScript
- **Build tool**: Vite
- **Styling**: Tailwind CSS 4
- **Routing**: React Router DOM v7
- **HTTP Client**: Axios
- **Forms**: React Hook Form

#### Structure
```
frontend/
├── src/
│   ├── components/
│   ├── pages/
│   └── services/
└── public/
```

## 📡 API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration

### Users
- `GET /api/users` - List all users
- `GET /api/users/{id}` - Get user details
- `POST /api/users` - Create a new user
- `PUT /api/users/{id}` - Update a user
- `DELETE /api/users/{id}` - Delete a user

## 🛠️ Useful commands

### Backend
```bash
# Access the backend container
docker compose exec backend sh

# Create an entity
docker compose exec backend php bin/console make:entity

# Create a migration
docker compose exec backend php bin/console make:migration

# Run migrations
docker compose exec backend php bin/console doctrine:migrations:migrate

# Load fixtures
docker compose exec backend php bin/console doctrine:fixtures:load

# Run tests
docker compose exec backend php bin/phpunit
```

### Frontend
```bash
# Access the frontend container
docker compose exec frontend sh

# Install a dependency
docker compose exec frontend npm install <package>

# Build for production
docker compose exec frontend npm run build

# Lint the code
docker compose exec frontend npm run lint
```

### Database
```bash
# Database dump
docker compose exec database pg_dump -U <username> <database_name> > backup.sql

# Restore a dump
docker compose exec -T database psql -U <username> <database_name> < backup.sql
```

## 🔧 Development

### Development mode
Volumes are mounted to enable hot-reload:
- Backend: PHP modifications are immediately taken into account
- Frontend: Vite HMR enabled (http://localhost:5173)

### Stop the application
```bash
# Stop containers
docker compose down

# Stop and remove volumes
docker compose down -v
```

## 📦 Main dependencies

### Backend
- Symfony Framework 7.3
- Doctrine ORM 3.5
- LexikJWTAuthenticationBundle 3.1
- NelmioCorsBundle 2.5
- PHPUnit 11.5

### Frontend
- React 19
- TypeScript 5.9
- Vite 7
- Tailwind CSS 4
- React Router DOM 7
- Axios 1.12

## 📝 License

Proprietary

## 👥 Contributors

Project developed as part of the T-DEV-700 module
