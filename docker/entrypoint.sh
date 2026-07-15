#!/bin/sh
set -e

# Railway inyecta $PORT dinámicamente.
# En el servidor de la alcaldía usamos el puerto 80 por defecto.
export APP_PORT="${PORT:-80}"

# Sustituir $APP_PORT en la plantilla de nginx y escribir la config final
envsubst '${APP_PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
