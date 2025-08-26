
echo "🚀 Iniciando aplicación en producción..."

echo "📁 Preparando directorios..."
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/app/public/clientes/fotos
mkdir -p storage/app/public/comprobantes/yape
mkdir -p storage/app/public/comprobantes/efectivo
mkdir -p storage/app/public/departamentos

chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "🔗 Verificando enlace simbólico de storage..."
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo "✅ Enlace simbólico creado exitosamente"
else
    echo "ℹ️ Enlace simbólico ya existe"
fi

echo "🧹 Limpiando cachés..."
php artisan optimize:clear

echo "⚡ Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "🔧 Optimizando autoloader..."
composer dump-autoload --optimize

echo "📊 Ejecutando migraciones..."
php artisan migrate --force

echo "🔄 Iniciando worker de colas..."
php artisan queue:work &

echo "🌐 Iniciando servidor en puerto 8000..."
php artisan serve --host=0.0.0.0 --port=8000