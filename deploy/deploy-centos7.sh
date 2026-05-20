#!/bin/bash
#
# Déploiement SENTRAVAUX sur CentOS 7 avec Docker
# Usage: sudo ./deploy/deploy-centos7.sh /opt/sentravaux
#
set -euo pipefail

APP_DIR="${1:-/opt/sentravaux}"
COMPOSE_CMD="docker compose"
HTTP_PORT="${SENTRAVAUX_HTTP_PORT:-8093}"
MYSQL_PORT="${SENTRAVAUX_MYSQL_PORT:-3313}"

echo "=============================================="
echo " SENTRAVAUX - Déploiement Docker (CentOS 7)"
echo " Répertoire: ${APP_DIR}"
echo " Port HTTP:  ${HTTP_PORT}"
echo " Port MySQL: ${MYSQL_PORT} (hôte)"
echo "=============================================="

if [ "$(id -u)" -ne 0 ]; then
    echo "Exécutez ce script en root (sudo)."
    exit 1
fi

install_docker() {
    if command -v docker >/dev/null 2>&1; then
        echo "[docker] Déjà installé: $(docker --version)"
        return 0
    fi
    echo "[docker] Installation Docker CE pour CentOS 7..."
    yum install -y yum-utils device-mapper-persistent-data lvm2
    yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
    yum install -y docker-ce docker-ce-cli containerd.io
    systemctl enable docker
    systemctl start docker
}

install_compose() {
    if docker compose version >/dev/null 2>&1; then
        echo "[compose] Plugin compose présent."
        return 0
    fi
    if command -v docker-compose >/dev/null 2>&1; then
        COMPOSE_CMD="docker-compose"
        echo "[compose] docker-compose v1 détecté."
        return 0
    fi
    echo "[compose] Installation docker-compose v2..."
    COMPOSE_VERSION="v2.24.5"
    mkdir -p /usr/local/lib/docker/cli-plugins
    curl -fsSL "https://github.com/docker/compose/releases/download/${COMPOSE_VERSION}/docker-compose-linux-x86_64" \
        -o /usr/local/lib/docker/cli-plugins/docker-compose
    chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
}

cd "${APP_DIR}"

if [ ! -f .env ]; then
    echo "[config] Création .env depuis .env.docker.example"
    cp .env.docker.example .env
    echo "⚠️  Éditez ${APP_DIR}/.env (mots de passe, LDAP, Oracle, APP_URL) puis relancez."
    exit 1
fi

# Ports dans .env
grep -q '^SENTRAVAUX_HTTP_PORT=' .env || echo "SENTRAVAUX_HTTP_PORT=${HTTP_PORT}" >> .env
grep -q '^SENTRAVAUX_MYSQL_PORT=' .env || echo "SENTRAVAUX_MYSQL_PORT=${MYSQL_PORT}" >> .env

if [ -x ./deploy/check-ports.sh ]; then
    ./deploy/check-ports.sh "${HTTP_PORT}" "${MYSQL_PORT}" || true
fi

install_docker
install_compose

echo "[build] Construction des images (peut prendre plusieurs minutes)..."
export DOCKER_BUILDKIT=1
${COMPOSE_CMD} build --no-cache app
${COMPOSE_CMD} build web

echo "[up] Démarrage des conteneurs..."
${COMPOSE_CMD} up -d

echo "[init] Liens storage et migrations..."
${COMPOSE_CMD} exec -T app php artisan storage:link --force 2>/dev/null || true
${COMPOSE_CMD} exec -T app php artisan migrate --force 2>/dev/null || true
${COMPOSE_CMD} exec -T app php artisan config:cache
${COMPOSE_CMD} exec -T app php artisan route:cache
${COMPOSE_CMD} exec -T app php artisan view:cache

SERVER_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
echo ""
echo "=============================================="
echo " Déploiement terminé."
echo " URL: http://${SERVER_IP:-localhost}:${HTTP_PORT}"
echo ""
echo " Commandes utiles:"
echo "   cd ${APP_DIR} && ${COMPOSE_CMD} ps"
echo "   cd ${APP_DIR} && ${COMPOSE_CMD} logs -f app"
echo "   cd ${APP_DIR} && ${COMPOSE_CMD} exec app php artisan users:sync-oracle"
echo "=============================================="
