#!/bin/bash
set -e

# ════════════════════════════════════════════════════════
#   Pro Encuestas — Script de Setup Completo
#   Uso: bash setup.sh
#        bash setup.sh --fresh   (borra volumes y recrea todo)
# ════════════════════════════════════════════════════════

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

FRESH=false
if [[ "$1" == "--fresh" ]]; then
    FRESH=true
fi

echo ""
echo -e "${BLUE}╔══════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     Pro Encuestas — Docker Setup         ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════╝${NC}"
echo ""

# ── Verificar dependencias ───────────────────────────
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker no está instalado.${NC}"
    exit 1
fi

if ! docker compose version &> /dev/null; then
    echo -e "${RED}❌ Docker Compose (plugin) no está instalado.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker y Docker Compose encontrados${NC}"

# ── Crear .env si no existe ──────────────────────────
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}📋 Creando .env desde .env.example...${NC}"
    cp .env.example .env

    # Ajustar variables para docker compose
    # DB_HOST apunta al servicio "db" de docker compose
    sed -i "s|DB_HOST=127.0.0.1|DB_HOST=db|g" .env
    sed -i "s|DB_HOST='127.0.0.1'|DB_HOST=db|g" .env

    # Limpiar comillas simples en variables de DB (problema en el .env.example)
    sed -i "s|DB_CONNECTION='pgsql'|DB_CONNECTION=pgsql|g" .env
    sed -i "s|DB_DATABASE='encuestas'|DB_DATABASE=encuestas|g" .env
    sed -i "s|DB_USERNAME='postgres'|DB_USERNAME=postgres|g" .env

    # Setear password de DB
    sed -i "s|# DB_PASSWORD=|DB_PASSWORD=secret|g" .env
    # Si la línea existe pero vacía
    if ! grep -q "^DB_PASSWORD=" .env; then
        echo "DB_PASSWORD=secret" >> .env
    fi

    # Entorno de producción para la imagen Docker
    sed -i "s|APP_ENV=local|APP_ENV=production|g" .env
    sed -i "s|APP_DEBUG=true|APP_DEBUG=false|g" .env
    sed -i "s|APP_URL=http://localhost|APP_URL=http://localhost:8080|g" .env
    sed -i "s|APP_NAME=Laravel|APP_NAME=Pro Encuestas|g" .env

    # Logs a stderr (visibles en docker logs)
    sed -i "s|LOG_CHANNEL=stack|LOG_CHANNEL=stderr|g" .env
    sed -i "s|LOG_LEVEL=debug|LOG_LEVEL=error|g" .env

    echo -e "${GREEN}✓ Archivo .env creado${NC}"
else
    echo -e "${GREEN}✓ Archivo .env ya existe${NC}"
fi

# ── Generar APP_KEY si está vacía ────────────────────
CURRENT_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2-)
if [ -z "$CURRENT_KEY" ]; then
    echo -e "${YELLOW}🔑 Generando APP_KEY...${NC}"
    # Generar key compatible con Laravel: base64 de 32 bytes aleatorios
    APP_KEY="base64:$(openssl rand -base64 32)"
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|g" .env
    echo -e "${GREEN}✓ APP_KEY generada y guardada en .env${NC}"
else
    echo -e "${GREEN}✓ APP_KEY ya configurada${NC}"
fi

# ── Si --fresh: borrar volumes y empezar limpio ──────
if [ "$FRESH" = true ]; then
    echo ""
    echo -e "${YELLOW}⚠️  Modo --fresh: eliminando volumes existentes...${NC}"
    docker compose -f docker-compose.local.yml down -v --remove-orphans 2>/dev/null || true
    echo -e "${GREEN}✓ Volumes eliminados${NC}"
fi

# ── Build y arrancar ─────────────────────────────────
echo ""
echo -e "${BLUE}🔨 Construyendo imagen Docker...${NC}"
echo -e "${YELLOW}   (Primera vez puede tardar 3-5 min)${NC}"
echo ""
docker compose -f docker-compose.local.yml build --no-cache 2>&1 | grep -E "(Step|=>|Error|error|Successfully)" || true

echo ""
echo -e "${BLUE}🚀 Arrancando servicios...${NC}"
docker compose -f docker-compose.local.yml up -d

# ── Esperar que la app esté lista ────────────────────
echo ""
echo -e "${YELLOW}⏳ Esperando que la app esté lista...${NC}"
MAX_WAIT=120
WAITED=0
until curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 | grep -qE "^(200|301|302|404)"; do
    sleep 3
    WAITED=$((WAITED + 3))
    if [ $WAITED -ge $MAX_WAIT ]; then
        echo -e "${RED}❌ La app no respondió en $MAX_WAIT segundos.${NC}"
        echo -e "${YELLOW}Revisa los logs con: docker compose -f docker-compose.local.yml logs -f app${NC}"
        exit 1
    fi
    echo -e "   Esperando... ${WAITED}s"
done

# ── Resultado final ──────────────────────────────────
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ Pro Encuestas está corriendo!       ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
echo -e "  🌐 App:      ${BLUE}http://localhost:8080${NC}"
echo -e "  🗄️  DB:       ${BLUE}localhost:5433${NC} (postgres/secret)"
echo ""
echo -e "  📋 Comandos útiles:"
echo -e "     ${YELLOW}docker compose -f docker-compose.local.yml logs -f app${NC}     → Ver logs"
echo -e "     ${YELLOW}docker compose -f docker-compose.local.yml exec app sh${NC}     → Entrar al contenedor"
echo -e "     ${YELLOW}docker compose -f docker-compose.local.yml down${NC}            → Apagar"
echo -e "     ${YELLOW}bash setup.sh --fresh${NC}          → Reinstalar todo"
echo ""
