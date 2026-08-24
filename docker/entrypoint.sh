#!/bin/sh
set -e

echo ""
echo "🚀 ================================================"
echo "   Pro Encuestas — Iniciando..."
echo "   ================================================"
echo ""

# ── 1. Permisos de storage y cache ──────────────────
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ── 2. Generar APP_KEY si no está configurada ────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:CHANGE_ME_RUN_php_artisan_key_generate" ]; then
    echo "🔑 APP_KEY no encontrada, generando automáticamente..."
    php artisan key:generate --force
    # Recargar la variable desde el .env que acabamos de escribir
    APP_KEY=$(grep "^APP_KEY=" /var/www/html/.env | cut -d '=' -f2-)
    export APP_KEY
    echo "   APP_KEY generada ✓"
else
    echo "🔑 APP_KEY configurada ✓"
fi

# ── 3. Esperar que la DB esté lista ─────────────────
echo "⏳ Esperando conexión a la base de datos..."
MAX_RETRIES=30
RETRY=0
until php artisan db:show --no-interaction 2>/dev/null | grep -q "pgsql" || [ $RETRY -eq $MAX_RETRIES ]; do
    RETRY=$((RETRY + 1))
    echo "   Intento $RETRY/$MAX_RETRIES..."
    sleep 2
done

if [ $RETRY -eq $MAX_RETRIES ]; then
    echo "❌ No se pudo conectar a la base de datos. Verifica DB_HOST, DB_PORT, DB_USERNAME y DB_PASSWORD."
    exit 1
fi
echo "   Base de datos lista ✓"

# ── 4. Migraciones y seeders ─────────────────────────
echo "📦 Ejecutando migraciones y seeders..."
php artisan migrate --seed --force
echo "   Migraciones completadas ✓"

# ── 5. Optimización para producción ─────────────────
echo "⚡ Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "   Optimización lista ✓"

echo ""
echo "✅ ================================================"
echo "   App lista en el puerto 80"
echo "   ================================================"
echo ""

# ── 6. Arrancar todos los servicios con Supervisor ──
exec /usr/bin/supervisord -c /etc/supervisord.conf
