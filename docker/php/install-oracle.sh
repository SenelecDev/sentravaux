#!/bin/bash
set -euo pipefail

ORACLE_DIR="${ORACLE_DIR:-/opt/oracle/instantclient}"
IC_BASE_URL="${IC_BASE_URL:-https://download.oracle.com/otn_software/linux/instantclient/1923000}"

install_from_zip() {
    local zip_path="$1"
    unzip -qo "$zip_path" -d /opt/oracle
    local extracted
    extracted=$(find /opt/oracle -maxdepth 1 -type d -name 'instantclient_*' | head -1)
    if [ -n "$extracted" ] && [ "$extracted" != "$ORACLE_DIR" ]; then
        ln -sfn "$extracted" "$ORACLE_DIR"
    fi
}

mkdir -p /opt/oracle

if [ -d "$ORACLE_DIR" ] || [ -L "$ORACLE_DIR" ]; then
    echo "[oracle] Client déjà présent dans $ORACLE_DIR"
elif [ -f /tmp/oracle/instantclient-basic.zip ]; then
    echo "[oracle] Installation depuis /tmp/oracle/*.zip"
    install_from_zip /tmp/oracle/instantclient-basic.zip
    if [ -f /tmp/oracle/instantclient-sdk.zip ]; then
        install_from_zip /tmp/oracle/instantclient-sdk.zip
    fi
else
    echo "[oracle] Téléchargement Instant Client 19.23..."
    cd /opt/oracle
    curl -fsSL -o basic.zip "${IC_BASE_URL}/instantclient-basic-linux.x64-19.23.0.0.0dbru.zip" || true
    curl -fsSL -o sdk.zip "${IC_BASE_URL}/instantclient-sdk-linux.x64-19.23.0.0.0dbru.zip" || true
    if [ -f basic.zip ]; then
        install_from_zip basic.zip
        [ -f sdk.zip ] && install_from_zip sdk.zip
        rm -f basic.zip sdk.zip
    else
        echo "[oracle] ATTENTION: Instant Client non installé. Placez les ZIP dans docker/oracle/ ou montez le client depuis l'hôte."
        exit 0
    fi
fi

if [ -d "$ORACLE_DIR" ] || [ -L "$ORACLE_DIR" ]; then
    echo "$ORACLE_DIR" > /tmp/oci8lib.conf
    docker-php-ext-configure oci8 --with-oci8=instantclient,"$ORACLE_DIR"
    docker-php-ext-install oci8
    echo "[oracle] Extension oci8 installée."
else
    echo "[oracle] oci8 non installé (client absent)."
fi
