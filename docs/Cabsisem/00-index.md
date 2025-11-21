# Documentación de Cabsisem

Bienvenido a la documentación oficial de Cabsisem. Esta guía cubre instalación, configuración, arquitectura, módulos, rutas (API y web), administración con Filament, internacionalización, y operación.

## Índice

- Introducción y arquitectura: `03-arquitectura.md`
- Instalación: `01-instalacion.md`
- Configuración (.env y servicios): `02-configuracion.md`
- Modelos y módulos: `04-modelos.md`
- Rutas API: `05-rutas-api.md`
- Rutas Web: `06-rutas-web.md`
- Administración (Filament): `07-filament.md`
- Internacionalización: `08-i18n.md`
- Operación y mantenimiento: `09-operacion.md`

## Convenciones

- Ambiente: Laravel 9, PHP 8+, MySQL 8.
- UI/Admin: Filament v2 + Livewire.
- Seguridad: Sanctum para API, Roles/Permisos (spatie/laravel-permission).
- Tiempo real: WebSockets (beyondcode/laravel-websockets + Pusher compatible).
- Exportación: Excel (maatwebsite/excel), PDF (dompdf).