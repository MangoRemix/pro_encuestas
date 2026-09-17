# ============================================================
# Stage 1: Build frontend assets
# Needs both Node.js AND PHP because @laravel/vite-plugin-wayfinder
# runs "php artisan wayfinder:generate" during the Vite build.
#
# Base Debian ("bookworm"), no Alpine: install-php-extensions (más abajo)
# solo tiene binarios PHP precompilados reales para glibc/Debian — en Alpine
# (musl) siempre termina compilando ICU/GD desde código fuente, que es el
# mayor cuello de botella del build (varios minutos). Con Debian, la
# instalación de extensiones baja de minutos a segundos.
# ============================================================
FROM php:8.3-fpm-bookworm AS node-builder

# Host de la API que el frontend compilado usará para todas sus llamadas axios.
# El default "/api/" (ruta relativa) funciona en cualquier dominio porque
# frontend y API se sirven desde el mismo origen (nginx en este mismo
# contenedor) — evita que un dominio mal configurado rompa todas las llamadas.
# Solo pasa un valor absoluto por --build-arg si de verdad la API vive en otro
# origen distinto al que sirve el frontend.
ARG VITE_APP_API_HOST=/api/

# Node.js 22 vía NodeSource: el paquete "nodejs" de Debian bookworm trae una
# versión demasiado vieja (18.x) para Vite 8 / este proyecto.
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl gnupg \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_22.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Install PHP extensions needed for artisan to bootstrap (binario precompilado).
ADD --chmod=755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql zip

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies (needed for artisan bootstrap)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# Install Node dependencies
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

# Copy full source
COPY . .

# Write a clean minimal .env — use openssl for APP_KEY (avoids artisan bootstrap issues)
# SQLite in-memory: no real DB needed just for wayfinder type generation
RUN APP_KEY="base64:$(openssl rand -base64 32)" \
    && printf "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=%s\nAPP_DEBUG=false\nDB_CONNECTION=sqlite\nDB_DATABASE=/tmp/temp.sqlite\nSESSION_DRIVER=array\nCACHE_STORE=array\nQUEUE_CONNECTION=sync\nFILESYSTEM_DISK=local\nLOG_CHANNEL=stderr\nVITE_APP_API_HOST=%s\n" "$APP_KEY" "$VITE_APP_API_HOST" > .env \
    && touch /tmp/temp.sqlite

# Pre-generate wayfinder route types so the Vite plugin finds them already done
RUN php artisan wayfinder:generate --with-form || true

# Build frontend assets
RUN npm run build


# ============================================================
# Stage 2: Install PHP dependencies (Composer)
# ============================================================
FROM composer:2.8 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# ============================================================
# Stage 3: Production image
# ============================================================
FROM php:8.3-fpm-bookworm AS production

# Install system runtime dependencies (no dev/compiler packages needed:
# install-php-extensions instala y limpia sus propias dependencias de build).
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        unzip \
        git \
        curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions. En Debian install-php-extensions usa binarios
# precompilados reales para casi todas estas — segundos en vez de minutos.
ADD --chmod=755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        pdo_pgsql \
        opcache \
        pcntl \
        bcmath \
        gd \
        zip \
        mbstring \
        intl

# Configure PHP-FPM to use unix socket
RUN echo '[www]' > /usr/local/etc/php-fpm.d/www.conf \
    && echo 'user = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'group = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'listen = /run/php/php8.3-fpm.sock' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'listen.owner = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'listen.group = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'listen.mode = 0660' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm = dynamic' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.max_children = 20' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.start_servers = 2' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.min_spare_servers = 1' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.max_spare_servers = 3' >> /usr/local/etc/php-fpm.d/www.conf

# Configure OPcache for production
RUN echo 'opcache.enable=1' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.memory_consumption=128' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.interned_strings_buffer=8' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.max_accelerated_files=10000' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.revalidate_freq=0' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.validate_timestamps=0' >> /usr/local/etc/php/conf.d/opcache.ini

# PHP production settings
RUN echo 'upload_max_filesize=50M' >> /usr/local/etc/php/conf.d/prod.ini \
    && echo 'post_max_size=50M' >> /usr/local/etc/php/conf.d/prod.ini \
    && echo 'memory_limit=256M' >> /usr/local/etc/php/conf.d/prod.ini \
    && echo 'max_execution_time=120' >> /usr/local/etc/php/conf.d/prod.ini

# Create php socket directory
RUN mkdir -p /run/php && chown www-data:www-data /run/php

# Set workdir
WORKDIR /var/www/html

# Copy application code
COPY --chown=www-data:www-data . .

# Copy vendor from composer stage
COPY --from=composer-builder --chown=www-data:www-data /app/vendor ./vendor

# Copy compiled frontend assets from node stage
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# Run composer scripts (post-autoload-dump)
RUN php artisan package:discover --ansi 2>/dev/null || true

# Copy docker configs (Debian's nginx package includes conf.d/*.conf; su
# sitio "default" en sites-enabled se quita para que no choque con el nuestro)
RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Create storage structure and set permissions
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
