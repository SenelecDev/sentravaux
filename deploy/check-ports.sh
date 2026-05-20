#!/bin/bash
# Vérifie que les ports proposés ne sont pas déjà utilisés sur le serveur CentOS
# Usage: ./deploy/check-ports.sh 8093 3313

set -euo pipefail

PORTS=("${@:-8093 3313}")

echo "=== Vérification des ports SENTRAVAUX ==="
for port in "${PORTS[@]}"; do
    if command -v ss >/dev/null 2>&1; then
        if ss -tuln | grep -q ":${port} "; then
            echo "❌ Port ${port} : OCCUPÉ"
            ss -tuln | grep ":${port} " || true
        else
            echo "✅ Port ${port} : libre"
        fi
    elif command -v netstat >/dev/null 2>&1; then
        if netstat -tuln | grep -q ":${port} "; then
            echo "❌ Port ${port} : OCCUPÉ"
        else
            echo "✅ Port ${port} : libre"
        fi
    else
        echo "⚠️  ss/netstat indisponible, test manuel requis pour ${port}"
    fi
done

echo ""
echo "Ports suggérés si conflit (4e application) :"
echo "  HTTP  : 8093, 8094, 8095, 9083"
echo "  MySQL : 3313, 3314, 3315 (exposition hôte optionnelle)"
