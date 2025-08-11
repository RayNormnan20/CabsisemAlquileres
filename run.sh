#!/bin/bash

echo "🚀 Iniciando aplicación en producción..."

# Ejecutar migraciones
echo "📊 Ejecutando migraciones..."
php artisan migrate --force

# Limpiar cachés existentes
echo "🧹 Limpiando cachés..."
php artisan optimize:clear

# Optimizar para producción
echo "⚡ Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimizar autoloader
echo "🔧 Optimizando autoloader..."
composer dump-autoload --optimize

# Crear directorios de caché si no existen
echo "📁 Preparando directorios de caché..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/views

# Lanzar worker de colas en background
echo "🔄 Iniciando worker de colas..."
php artisan queue:work &

# Iniciar servidor
echo "🌐 Iniciando servidor en puerto 8000..."
php artisan serve --host=0.0.0.0 --port=8000
