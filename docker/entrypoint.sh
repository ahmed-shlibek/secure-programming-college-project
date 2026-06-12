#!/bin/sh
set -e

# Render injects $PORT at runtime. Fall back to 10000 for local runs.
# Apache resolves the ${PORT} placeholders in ports.conf / the vhost from
# this exported environment variable, so it must be present before starting.
export PORT="${PORT:-10000}"

# Render mounts secret files readable only by root, but Apache's PHP workers
# run as www-data and can't open them (PDO: "failed loading cafile stream").
# This entrypoint runs as root, so copy the CA to a world-readable path and
# repoint DB_SSL_CA at the copy before starting Apache (which inherits the env).
if [ -n "${DB_SSL_CA:-}" ]; then
    if [ -f "${DB_SSL_CA}" ]; then
        if cp "${DB_SSL_CA}" /usr/local/share/db-ca.pem 2>/dev/null; then
            chmod 0644 /usr/local/share/db-ca.pem
            export DB_SSL_CA=/usr/local/share/db-ca.pem
            echo "[entrypoint] DB CA copied to /usr/local/share/db-ca.pem (readable by www-data)"
        else
            echo "[entrypoint] WARNING: could not copy DB_SSL_CA (${DB_SSL_CA})"
        fi
    else
        echo "[entrypoint] WARNING: DB_SSL_CA set but file not found: ${DB_SSL_CA}"
    fi
fi

exec "$@"