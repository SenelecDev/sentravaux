# Déploiement SENTRAVAUX sur CentOS 7 (Docker)

Guide pour déployer la **4ᵉ application** sur un serveur CentOS 7 qui héberge déjà 3 applications similaires.

## Ports par défaut (à adapter)

| Service | Port hôte | Remarque |
|---------|-----------|----------|
| HTTP SENTRAVAUX | **8093** | Évite 8080–8092 souvent utilisés |
| MySQL (optionnel) | **3313** | Évite 3306–3312 ; interne = 3306 |

Vérification sur le serveur :

```bash
chmod +x deploy/check-ports.sh
./deploy/check-ports.sh 8093 3313
```

Si un port est occupé, modifiez dans `.env` :

```env
SENTRAVAUX_HTTP_PORT=8094
SENTRAVAUX_MYSQL_PORT=3314
```

## Prérequis serveur

- CentOS 7.x, accès root
- Accès réseau vers LDAP (`10.101.2.30`) et Oracle (`10.101.3.119`)
- 4 Go RAM minimum recommandé pour MySQL + PHP

## 1. Copier le projet sur le serveur

```bash
sudo mkdir -p /opt/sentravaux
sudo chown $USER:$USER /opt/sentravaux

# Depuis votre poste (exemple rsync)
rsync -avz --exclude node_modules --exclude vendor --exclude .git \
  ./ user@serveur:/opt/sentravaux/
```

Ou `git clone` si dépôt disponible.

## 2. Configuration

```bash
cd /var/www/sentravaux   # ou /opt/sentravaux
cp .env.docker.example .env
nano .env
```

Le fichier `.env` doit exister **sur le serveur** dans le dossier du projet : il est monté dans le conteneur `app` (il n'est pas inclus dans l'image Docker).

Variables importantes :

- `APP_KEY` : générer avec `php artisan key:generate` ou laisser vide (généré au 1er démarrage)
- `APP_URL=http://IP_SERVEUR:8093`
- `DB_PASSWORD` : mot de passe fort
- `LDAP_*`, `ORACLE_*` : identifiants réels
- `RUN_MIGRATIONS=true` pour la première installation

## 3. Oracle Instant Client (si sync RH)

Si le build échoue sur oci8, copiez les ZIP Oracle dans `docker/oracle/` (voir `docker/oracle/README.md`) puis :

```bash
docker compose build --no-cache app
```

Si les autres apps utilisent déjà le client sur l’hôte :

```bash
# Exemple chemin CentOS
ls /usr/lib/oracle/*/client64/lib
```

Créez `docker-compose.override.yml` :

```yaml
services:
  app:
    volumes:
      - /usr/lib/oracle/19/client64/lib:/opt/oracle/instantclient:ro
    environment:
      LD_LIBRARY_PATH: /opt/oracle/instantclient
```

## 4. Déploiement automatique

```bash
chmod +x deploy/deploy-centos7.sh deploy/check-ports.sh
sudo ./deploy/deploy-centos7.sh /opt/sentravaux
```

## 5. Déploiement manuel

```bash
cd /opt/sentravaux

# Installer Docker (une fois)
sudo ./deploy/deploy-centos7.sh /opt/sentravaux  # échouera sans .env, installe Docker

# Build & run
docker compose build
docker compose up -d

docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --class=RolesAndPermissionsSeeder
```

## 6. MySQL externe (sans conteneur db)

Si MySQL tourne déjà sur le serveur pour les autres apps :

```bash
# .env
DB_HOST=172.17.0.1
DB_PORT=3306
DB_DATABASE=sentravaux
DB_USERNAME=...
DB_PASSWORD=...

docker compose -f docker-compose.yml -f docker-compose.external-db.yml up -d
```

Créez la base `sentravaux` dans MySQL avant migration.

## 7. Reverse proxy Nginx hôte (optionnel)

Pour exposer en HTTPS sur le port 443 existant :

```nginx
# /etc/nginx/conf.d/sentravaux.conf
upstream sentravaux {
    server 127.0.0.1:8093;
}
server {
    listen 443 ssl;
    server_name sentravaux.senelec.sn;
    location / {
        proxy_pass http://sentravaux;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Dans `.env` : `APP_URL=https://sentravaux.senelec.sn`

## 8. Commandes utiles

```bash
docker compose ps
docker compose logs -f app
docker compose exec app php artisan cache:clear
docker compose exec app php artisan users:sync-oracle
docker compose restart
```

## 9. Mise à jour

```bash
cd /opt/sentravaux
git pull   # ou rsync
docker compose build app web
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
```

## Architecture

```
[Navigateur] → :8093 → [nginx web] → [php-fpm app] → [mysql db]
                              ↘ LDAP / Oracle (réseau interne)
```

## Dépannage

| Problème | Solution |
|----------|----------|
| Port déjà utilisé | Changer `SENTRAVAUX_HTTP_PORT` dans `.env` |
| Erreur 500 | `docker compose logs app` |
| Permission storage | `docker compose exec -u root app chown -R www-data:www-data storage bootstrap/cache` |
| LDAP inaccessible | Vérifier firewall ; tester `docker compose exec app ping 10.101.2.30` |
| oci8 manquant | Voir `docker/oracle/README.md` |
