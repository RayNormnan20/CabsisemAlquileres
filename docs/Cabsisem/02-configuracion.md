# Configuración (.env y servicios)

Principales variables de entorno y servicios usados por Cabsisem.

## Base

- `APP_NAME`, `APP_ENV`, `APP_URL`
- `LOG_CHANNEL`, `TIMEZONE`, `LOCALE`
- `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## Cache/Sesión/Cola

- `CACHE_DRIVER` (recomendado `redis` o `file`)
- `SESSION_DRIVER` (`file`/`database`)
- `QUEUE_CONNECTION` (si usas jobs en segundo plano)

## Sanctum

- `SANCTUM_STATEFUL_DOMAINS` para SPA/Apps que consumen el API.
- Guard: `web` (ver `config/sanctum.php`).

## WebSockets / Pusher

- `BROADCAST_DRIVER=pusher`
- `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER`
- Si usas `beyondcode/laravel-websockets`, configura su host/puerto y Supervisor (ver `MANUAL.txt`).

## Filament / Autenticación

- Filament Breezy: ver `config/filament-breezy.php` (modelo de usuario, broker de reset, etc.).

## Correo

- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`

## Settings

- `spatie/laravel-settings`: valores iniciales definidos en `database/settings/*` (ej. `general.site_name`).