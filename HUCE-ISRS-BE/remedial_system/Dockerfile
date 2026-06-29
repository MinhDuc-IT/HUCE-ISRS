# ────────────────────────────────────────────────────────────
# Stage 1: Composer install (PHP CLI)
# ────────────────────────────────────────────────────────────
FROM php:8.2-cli-alpine AS composer-stage

# Install system deps cần cho Composer + extensions
RUN apk add --no-cache \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev

# Build extensions
RUN docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo_mysql \
        zip \
        intl \
        bcmath \
        mbstring \
        opcache

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
# Production: no dev deps, optimize autoloader
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# ────────────────────────────────────────────────────────────
# Stage 2: PHP-FPM Production Image
# ────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="HUCE-ISRS Team"

# Install runtime libs
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    libzip \
    icu-libs \
    oniguruma \
    libgomp

# Cài extensions (dùng lại config từ stage 1)
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
 && docker-php-ext-configure gd --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
        gd \
        pdo_mysql \
        zip \
        intl \
        bcmath \
        mbstring \
        opcache \
        pcntl \
 && apk del libpng-dev libjpeg-turbo-dev libzip-dev icu-dev oniguruma-dev

# Cài phpredis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del .build-deps

# PHP-FPM & OPcache config
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Copy vendor từ stage 1
COPY --from=composer-stage /app/vendor ./vendor

# Copy toàn bộ application
COPY . .

# Khôi phục script autoload (package:discover) sau khi đã có đủ source code
RUN php artisan package:discover --ansi

# Permissions cho storage và cache
RUN mkdir -p storage/framework/{cache,sessions,views} \
    storage/logs \
    bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Artisan entrypoint
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
