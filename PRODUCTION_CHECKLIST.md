Checklist - Preparación para producción

Resumen de acciones realizadas automáticamente por el asistente:
- Reemplazado `Route::get('/', function(){...})` por `Route::redirect('/', '/login')` en `routes/web.php` para permitir `php artisan route:cache`.
- Ejecutado `php artisan storage:link` para exponer `storage/app/public` vía `public/storage`.
- Añadido y optimizado el template PDF `resources/views/invoices/pdf.blade.php`:
  - Fuente DejaVu cargada desde `vendor/dompdf/dompdf/lib/fonts` (file://).
  - Logo escalable (`max-width: 150px; height: auto;`).
  - Eliminados `position: fixed` y floats problemáticos.
  - Añadido comando Artisan temporal `invoices:pdf {id}` que guarda PDF en `storage/app/public`.
- Ejecutados: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.

Recomendaciones y pasos manuales (debes ejecutar en el servidor de producción):

1) Variables de entorno (.env)
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` con la URL pública (https)
- `APP_KEY` debe existir y ser segura (32 caracteres).
- `SESSION_DRIVER=redis` o `database` según infra.
- `CACHE_STORE=redis` o `memcached` en producción.
- `QUEUE_CONNECTION=redis` o `sqs` para queues.
- `LOG_CHANNEL=daily` o `stderr` (dependiendo infra) y `LOG_LEVEL=info`.
- `SESSION_SECURE_COOKIE=true` (si usas HTTPS) y `SESSION_HTTP_ONLY=true`.

2) Servidor web
- Usar Nginx + PHP-FPM. Configurar `fastcgi_param` y headers de seguridad (HSTS, X-Frame-Options, X-Content-Type-Options).
- Asegurar `public` es el DocumentRoot.

3) Base de datos
- Verificar índices y ejecutar migraciones: `php artisan migrate --force`.
- Revisar índices en tablas grandes (invoices, invoice_items, products) y añadir índices para columnas usadas en filtros/sorts.

4) Cache y optimizaciones
- Ejecutar `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` (ya ejecutados aquí).
- Opcional: habilitar OPCache en PHP y ajustar `opcache.memory_consumption`.

5) Colas y jobs
- Configurar `supervisor` o systemd para `php artisan queue:work --sleep=3 --tries=3 --timeout=90`.
- Configurar `failed_jobs` table y alerting.

6) Seguridad en el código
- Formularios: usar `@csrf` (ya presente).
- Validaciones: usar FormRequests (ya hay varios, p.ej. `StoreInvoiceRequest`).
- Evitar outputs sin escapar (`{!!`) en vistas de usuario (no se detectaron en `resources/views`).
- Evitar consultas dinámicas con concatenación; prefer bindings y Eloquent.

7) Logs y monitoreo
- Rotar logs (`daily`) y enviar errores críticos a Sentry/Slack si aplica.

8) Tests
- Ejecutar test suite: `composer test` o `php artisan test`.

9) Backups
- Configurar backups periódicos de la BD y del storage.

Cambios pendientes que puedo aplicar si me confirmas:
- Revisar y aplicar índices recomendados según consultas lentas (necesito logs o `EXPLAIN` de consultas lentas).
- Reforzar CSP headers y políticas de seguridad HTTP.
- Integrar un sistema de monitoreo (Sentry) y health checks.

Comandos rápidos para producción (ejecutar en el servidor):

```powershell
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan queue:restart
``` 

Si quieres, procedo ahora a:
- Ejecutar un scan más profundo por N+1 y aplicar eager-loading en controladores críticos.
- Revisar consultas concretas lentas (proporciona logs o queries lentas si las tienes).
- Añadir un archivo `docs/production.md` con pasos de despliegue detallados.

---
Documento generado automáticamente como paso intermedio; puedo actualizarlo según me indiques.
