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

# ── 2. Verificar APP_KEY ─────────────────────────────
# En Coolify/producción la APP_KEY se inyecta como variable de entorno.
# En local (docker-compose + setup.sh) ya viene en el .env montado.
if [ -z "$APP_KEY" ]; then
    echo "⚠️  APP_KEY no encontrada."
    # Solo intentar generar si existe el .env (entorno local)
    if [ -f "/var/www/html/.env" ]; then
        echo "🔑 Generando APP_KEY automáticamente..."
        php artisan key:generate --force
    else
        echo "❌ ERROR: APP_KEY es requerida. Configúrala como variable de entorno."
        exit 1
    fi
else
    echo "🔑 APP_KEY configurada ✓"
fi

# ── 3. Esperar que la DB esté lista ─────────────────
echo "⏳ Esperando conexión a la base de datos..."
MAX_RETRIES=30
RETRY=0
until php -r "
    try {
        \$pdo = new PDO(
            'pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE}',
            '${DB_USERNAME}',
            '${DB_PASSWORD}'
        );
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    RETRY=$((RETRY + 1))
    if [ $RETRY -ge $MAX_RETRIES ]; then
        echo "❌ No se pudo conectar a la base de datos después de $MAX_RETRIES intentos."
        echo "   Verifica DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD."
        exit 1
    fi
    echo "   Intento $RETRY/$MAX_RETRIES — esperando 3s..."
    sleep 3
done
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
