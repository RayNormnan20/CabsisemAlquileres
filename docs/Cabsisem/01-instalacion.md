# Instalación

## Requisitos

- `PHP >= 8.0.2`, `Composer`
- `Node.js` y `npm`/`yarn`
- `MySQL 8`
- Cuenta/clave Pusher o servidor WebSockets configurado

## Pasos rápidos (Windows/Laragon)

1. Clonar el proyecto en `c:\laragon\www\Cabsisem`.
2. Ejecutar scripts de instalación definidos en `composer.json`:
   - Windows: `composer run install-project-win`
   - Linux/Mac: `composer run install-project`
   Estos scripts realizan: `npm install`, `composer install`, `npm run build`, `composer dump-autoload`, crean `.env`, `php artisan key:generate`, `php artisan migrate`, `php artisan db:seed`.
3. Configurar `.env` (ver `02-configuracion.md`).
4. Iniciar servidor local y acceder a `/login`.

## Semillas y datos iniciales

- Revisa los `Database\Seeders` para usuarios/roles por defecto.
- Ajusta las semillas de catálogo (monedas, tipos) según tu operación.

## Assets y Vite

- Construye assets con `npm run build`.
- Para desarrollo, puedes usar `npm run dev`.