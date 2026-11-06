# Authentication & Authorization API (`authentication-api`)

Service central d'authentification (SSO) et de gestion des autorisations (RBAC : Utilisateurs, Rôles, Permissions, Applications) propulsé par **Laravel 12** et **Laravel Sanctum 4**.

---

## Prérequis & Stack

- **PHP** 8.2+ (PHP 8.3 FPM sous Docker)
- **Laravel** 12 & **Laravel Sanctum** 4
- **MySQL** / **SQLite** (tests)

---

## Installation & Initialisation

### Via Docker (Recommandé)

```bash
# Migration, seeders (applications + permissions) et création de l'administrateur
docker exec -it authentication-api php artisan migrate:fresh --seed \
  && docker exec -it authentication-api php artisan app:create-admin admin 123456 --email=admin@admin.com
```

### En local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan app:create-admin admin 123456 --email=admin@admin.com
```

---

## Endpoints Principaux (`/api`)

| Méthode | Route | Protection | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/login` | `throttle:login` (5/min) | Authentifie un utilisateur et retourne un Bearer token Sanctum |
| `GET` | `/api/validate-token` | `auth:sanctum` | Valide le token et retourne l'utilisateur, ses rôles et ses permissions (SSO inter-services) |
| `POST` | `/api/logout` | `auth:sanctum` | Révoque le token courant |
| `CRUD` | `/api/user` | `auth:sanctum`, `can:user-*` | Gestion des utilisateurs |
| `CRUD` | `/api/role` | `auth:sanctum`, `can:role-*` | Gestion des rôles |
| `CRUD` | `/api/permission` | `auth:sanctum`, `can:permission-*` | Gestion des permissions |
| `CRUD` | `/api/role_user` | `auth:sanctum`, `can:role_user-*` | Attribution des rôles aux utilisateurs |
| `CRUD` | `/api/permission_user` | `auth:sanctum`, `can:permission_user-*` | Attribution directe de permissions aux utilisateurs |
| `CRUD` | `/api/permission_role` | `auth:sanctum`, `can:permission_role-*` | Attribution de permissions aux rôles |
| `CRUD` | `/api/app` | `auth:sanctum`, `can:app-*` | Gestion des applications clientes |

> Chaque route `CRUD` expose : `GET /` (`list`), `GET /{id}` (`show`), `POST /` (`create`), `PUT /{id}` (`update`), `DELETE /{id}` (`delete`).

---

## Tests

```bash
php artisan test
```
