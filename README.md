# SENTRAVAUX - Gestion des Travaux SENELEC

Application de gestion des demandes de travaux (Laravel 10).

**Dépôt GitHub :** [https://github.com/SenelecDev/sentravaux](https://github.com/SenelecDev/sentravaux)

## Stack technique

- **Framework** : Laravel 10
- **Frontend** : Tailwind CSS 4, Alpine.js 3, Livewire 3
- **Auth** : LDAP (LdapRecord) + authentification locale
- **Rôles** : Spatie Laravel Permission
- **Base de données** : MySQL
- **Intégrations** : LDAP, Oracle RH (sync utilisateurs), notifications in-app

## Installation (développement — Laragon)

```bash
cd c:\laragon\www
git clone https://github.com/SenelecDev/sentravaux.git sentravaux
cd sentravaux
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configurer `.env` (base `sentravaux`, LDAP, Oracle si besoin) puis :

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

## Installation (Docker)

```bash
git clone https://github.com/SenelecDev/sentravaux.git
cd sentravaux
cp .env.docker.example .env
# Éditer .env (DB, LDAP, Oracle, ports)
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --seed
```

Ports par défaut : HTTP **8093**, MySQL hôte **3313** (modifiables dans `.env`).

## Déploiement CentOS 7

Voir le guide : [deploy/DEPLOY-CENTOS7.md](deploy/DEPLOY-CENTOS7.md)

```bash
chmod +x deploy/*.sh
sudo ./deploy/deploy-centos7.sh /opt/sentravaux
```

## Compte par défaut (seed)

| Matricule | Mot de passe | Rôle  |
|-----------|--------------|-------|
| ADMIN     | password     | admin |

## Configuration LDAP (.env)

```env
LDAP_ENABLED=true
LDAP_HOST=10.101.2.30
LDAP_USERNAME="CN=...,CN=Users,DC=electricite,DC=sn"
LDAP_PASSWORD=***
LDAP_PORT=3268
LDAP_BASE_DN="DC=electricite,DC=sn"
```

`LDAP_ENABLED=false` → authentification locale uniquement.

## Commandes utiles

```bash
php artisan users:sync-oracle    # Sync RH Oracle
php artisan migrate --seed
npm run build
make up                          # Docker
```

## Structure principale

```
app/Http/Controllers/     # Contrôleurs métier (SAD, SEG, UMT, UMR, …)
app/Services/NotificationService.php
docker/                   # Images PHP, Nginx
deploy/                   # Scripts CentOS 7
resources/views/          # Vues Blade
```

## Licence

Usage interne SENELEC.
