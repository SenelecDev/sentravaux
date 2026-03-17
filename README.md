# SENTRAVAUX - Gestion des Travaux SENELEC

## Stack Technique

- **Framework** : Laravel 10
- **Frontend** : Tailwind CSS 4, Alpine.js 3, Livewire 3
- **Auth** : LDAP (LdapRecord) + Auth locale
- **Roles** : Spatie Laravel Permission
- **Base de donnees** : MySQL (sentravaux)

## Installation

```bash
cd c:\laragon\www
git clone <repo-url> sentravaux
cd sentravaux
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configurer .env : DB_DATABASE=sentravaux
php artisan migrate --seed
npm run build
```

## Comptes par defaut

| Matricule | Mot de passe | Role  |
|-----------|-------------|-------|
| ADMIN     | password    | admin |

## Design System SENELEC

### Couleurs
- Purple : #2B1444 (sidebar)
- Magenta : #B3006C (header, accents)
- Teal : #0A91A3 (secondaire)
- Orange : #E87400 (alertes)
- Blue : #0D1CB0 (liens)
- Yellow : #FFD100 (highlights)

### Classes CSS
- Boutons : .btn-senelec, .btn-primary, .btn-secondary, .btn-success, .btn-danger
- Cartes : .card-senelec, .card-glass
- Inputs : .input-senelec, .select-senelec
- Badges : .badge-senelec, .badge-success, .badge-warning, .badge-danger
- Tables : .table-senelec

### Polices
- Conthrax : Titres (public/fonts/)
- Rajdhani : Headings
- Open Sans : Corps de texte

## Structure

```
app/Http/Controllers/
  Auth/LoginController.php        # Auth locale + LDAP
  DashboardController.php         # Dashboard
  ProfileController.php           # Profil
  Admin/DashboardController.php   # Admin dashboard
  Admin/UserController.php        # CRUD users
app/Models/User.php               # LDAP + Spatie
app/Ldap/LdapAttributeHandler.php # Sync LDAP
resources/css/app.css             # Design system
resources/js/app.js               # Alpine.js
resources/views/layouts/          # Layout + partials
resources/views/auth/login.blade.php
resources/views/admin/            # Pages admin
```

## Configuration LDAP

Dans .env :
```
LDAP_ENABLED=true
LDAP_HOST=votre-serveur-ldap
LDAP_USERNAME=cn=admin,dc=senelec,dc=sn
LDAP_PASSWORD=secret
LDAP_PORT=389
LDAP_BASE_DN=dc=senelec,dc=sn
```

LDAP_ENABLED=false = auth locale uniquement.

## Personnalisation

- Nom : .env APP_NAME
- Logo : public/img/logo.png
- Couleurs : @theme dans resources/css/app.css
- Sidebar : resources/views/layouts/partials/sidebar.blade.php
- Roles : database/seeders/RolesAndPermissionsSeeder.php
