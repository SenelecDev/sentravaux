#!/bin/bash
set -e

# Ne pas faire planter le conteneur si migrate/cache échoue au démarrage (DB pas encore prête)
handle_startup_cmd() {
    "$@" || echo "[entrypoint] Avertissement: échec de la commande: $*"
}

cd /var/www/html

fix_permissions() {
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true
}

wait_for_db() {
    if [ -z "${DB_HOST:-}" ]; then
        return 0
    fi
    echo "[entrypoint] Attente MySQL (${DB_HOST}:${DB_PORT:-3306})..."
    for i in $(seq 1 60); do
        if php -r "new PDO('mysql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT')?:'3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; then
            echo "[entrypoint] MySQL disponible."
            return 0
        fi
        sleep 2
    done
    echo "[entrypoint] MySQL non joignable après 120s."
    return 1
}

fix_permissions

if [ "${APP_ENV:-local}" = "production" ]; then
    wait_for_db || true

    if [ ! -f .env ]; then
        echo "[entrypoint] Fichier .env manquant."
        exit 1
    fi

    if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "" ]; then
        php artisan key:generate --force
    fi

    php artisan storage:link --force 2>/dev/null || true

    if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
        handle_startup_cmd php artisan migrate --force
    fi

    handle_startup_cmd php artisan config:cache
    handle_startup_cmd php artisan route:cache
    handle_startup_cmd php artisan view:cache
fi

fix_permissions

exec "$@"
