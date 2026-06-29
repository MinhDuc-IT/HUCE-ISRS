# ────────────────────────────────────────────────────────────
# Stage 1: Composer install
# ────────────────────────────────────────────────────────────
FROM php:8.2-cli-alpine AS composer-stage

# MS ODBC Driver cần glibc – dùng Debian cho stage này
FROM php:8.2-cli-bookworm AS composer-stage

# Cài ODBC + SQL Server driver (pdo_sqlsrv cần msodbcsql)
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        gnupg \
        unzip \
        git \
    && curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl https://packages.microsoft.com/config/debian/12/prod.list \
        > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
        msodbcsql18 \
        unixodbc-dev \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# ────────────────────────────────────────────────────────────
# Stage 2: PHP-FPM Production (Debian – cần cho msodbcsql)
# ────────────────────────────────────────────────────────────
FROM php:8.2-fpm-bookworm AS production

LABEL maintainer="HUCE-ISRS Team"

# Cài MS ODBC 18 + SQL Server driver
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        gnupg \
        unixodbc \
    && curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl https://packages.microsoft.com/config/debian/12/prod.list \
        > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
        msodbcsql18 \
        unixodbc-dev \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Thêm PHP extensions cơ bản
RUN docker-php-ext-install -j$(nproc) \
        pdo \
        mbstring \
        opcache \
        bcmath

# OPcache config
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

# Copy vendor từ stage 1
COPY --from=composer-stage /app/vendor ./vendor

# Copy toàn bộ source
COPY . .

# Khôi phục script autoload (package:discover) sau khi đã có đủ source code
RUN php artisan package:discover --ansi

# Tạo SQLite database file cho api_clients
RUN mkdir -p database \
    && touch database/database.sqlite \
    && mkdir -p storage/framework/{cache,sessions,views} \
       storage/logs \
       bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache

COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
