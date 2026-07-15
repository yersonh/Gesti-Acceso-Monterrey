FROM php:8.3-fpm-alpine

# Dependencias del sistema necesarias para las extensiones PHP
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    postgresql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    curl-dev \
    gettext

# Extensiones PHP (idénticas al nixpacks.toml anterior)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        curl \
        gd \
        mbstring \
        xml \
        zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar dependencias primero para aprovechar el cache de capas Docker
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

# Copiar el resto de la app
COPY . .

# Generar autoloader final y crear directorios necesarios
RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p storage/cache storage/logs uploads \
    && chown -R www-data:www-data /app

# Copiar configuraciones del servidor
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/supervisord.conf    /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh       /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
