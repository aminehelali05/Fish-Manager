#!/usr/bin/env bash
set -euo pipefail

echo "[entrypoint] cleaning up MPM modules..." >&2
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf || true
rm -f /etc/apache2/mods-available/mpm_event.load /etc/apache2/mods-available/mpm_event.conf \
      /etc/apache2/mods-available/mpm_worker.load /etc/apache2/mods-available/mpm_worker.conf || true

echo "[entrypoint] ensuring mpm_prefork and rewrite are enabled" >&2
a2enmod mpm_prefork || true
a2enmod rewrite || true

echo "[entrypoint] enabled mods:" >&2
ls -l /etc/apache2/mods-enabled || true

echo "[entrypoint] list mpm occurrences:" >&2
grep -R "mpm_" /etc/apache2 || true

# Replace Listen port if PORT env var is provided
export PORT=${PORT:-80}
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf || true

echo "[entrypoint] starting apache on port ${PORT}" >&2
exec apache2-foreground
