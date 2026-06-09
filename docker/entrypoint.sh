#!/bin/sh
set -e

# Render injects $PORT at runtime. Fall back to 10000 for local runs.
# Apache resolves the ${PORT} placeholders in ports.conf / the vhost from
# this exported environment variable, so it must be present before starting.
export PORT="${PORT:-10000}"

exec "$@"