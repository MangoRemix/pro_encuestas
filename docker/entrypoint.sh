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
if [ -z "$APP_KEY" ]; then
    echo "⚠️  APP_KEY no encontrada."
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
# Usar valores por defecto seguros si las variables están vacías
TARGET_DB_HOST="${DB_HOST:-db}"
TARGET_DB_PORT="${DB_PORT:-5432}"
TARGET_DB_NAME="${DB_DATABASE:-encuestas}"
TARGET_DB_USER="${DB_USERNAME:-postgres}"
TARGET_DB_PASS="${DB_PASSWORD:-secret}"

# Si las variables venían como cadenas vacías, forzar defaults
[ -z "$TARGET_DB_NAME" ] && TARGET_DB_NAME="encuestas"
[ -z "$TARGET_DB_USER" ] && TARGET_DB_USER="postgres"
[ -z "$TARGET_DB_PASS" ] && TARGET_DB_PASS="secret"

echo "⏳ Esperando conexión a la base de datos (${TARGET_DB_HOST}:${TARGET_DB_PORT}/${TARGET_DB_NAME} como ${TARGET_DB_USER})..."

MAX_RETRIES=30
RETRY=0
until DB_ERR=$(php -r "
    try {
        \$pdo = new PDO(
            'pgsql:host=${TARGET_DB_HOST};port=${TARGET_DB_PORT};dbname=${TARGET_DB_NAME}',
            '${TARGET_DB_USER}',
            '${TARGET_DB_PASS}'
        );
        exit(0);
    } catch (Exception \$e) {
        echo \$e->getMessage();
        exit(1);
    }
" 2>&1); do
    RETRY=$((RETRY + 1))
    if [ $RETRY -ge $MAX_RETRIES ]; then
        echo "❌ No se pudo conectar a la base de datos después de $MAX_RETRIES intentos."
        echo "   Último error: $DB_ERR"
        echo "   Host: $TARGET_DB_HOST, DB: $TARGET_DB_NAME, User: $TARGET_DB_USER"
        exit 1
    fi
    echo "   Intento $RETRY/$MAX_RETRIES — error: $DB_ERR (esperando 3s...)"
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
