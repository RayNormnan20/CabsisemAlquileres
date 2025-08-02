#!/bin/bash

# Lanzar el worker de colas en background
php artisan queue:work &

# Ejecutar migraciones (para evitar errores, espera a que termine)
php artisan migrate --force

# Ejecutar seeders solo si es necesario (por ejemplo, ambiente dev)
php artisan db:seed --force

# Construir assets (en producción, mejor que esto se haga antes en build)
npm run build

# Limpiar y optimizar cache de Laravel
php artisan optimize:clear

# Generar clave si no existe (puede ser útil)
if [ ! -f /var/www/html/storage/oauth-private.key ]; then
  php artisan key:generate
fi

# Finalmente, levantar el servidor integrado de Laravel
php artisan serve --host=0.0.0.0 --port=8000
