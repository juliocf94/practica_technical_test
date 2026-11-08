#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

# La configuración va en el .env: "artisan serve" no pasa las variables de entorno
# del contenedor a sus procesos hijos, así que DB_DATABASE debe estar en el archivo.
DB_PATH=/var/www/html/database/data/database.sqlite
sed -i '/^#\{0,1\} *DB_DATABASE=/d' .env
echo "DB_DATABASE=$DB_PATH" >> .env

# Respuestas de error limpias (sin stack trace) para quien evalúa la API
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction
fi

# Base de datos SQLite, se crea
mkdir -p database/data
if [ ! -f database/data/database.sqlite ]; then
    touch database/data/database.sqlite
    php artisan migrate --force --seed --no-interaction
else
    php artisan migrate --force --no-interaction
fi

php artisan config:clear >/dev/null

echo ""
echo "  API de Tareas lista en http://localhost:${APP_PORT:-8000}/api/tareas"
echo "  Usuario de prueba: test@example.com / password"
echo ""

exec "$@"
