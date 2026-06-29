# ────────────────────────────────────────────────────────────
# Stage 1: Composer install
# ────────────────────────────────────────────────────────────
# MS ODBC Driver cần glibc – dùng Debian cho stage này
FROM php:8.2-cli-bookworm AS composer-stage

# Cài ODBC + SQL Server driver (pdo_sqlsrv cần msodbcsql + build tools)
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        gnupg \
        unzip \
        git \
        g++ \
        make \
        autoconf \
        pkg-config \
        $PHPIZE_DEPS \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
        msodbcsql18 \
        unixodbc-dev \
    && pecl install sqlsrv-5.11.1 pdo_sqlsrv-5.11.1 \
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

# Cài MS ODBC 18, SQL Server driver và PHP extensions cơ bản
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        gnupg \
        unixodbc \
        unixodbc-dev \
        g++ \
        make \
        autoconf \
        pkg-config \
        $PHPIZE_DEPS \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
        msodbcsql18 \
    && pecl install sqlsrv-5.11.1 pdo_sqlsrv-5.11.1 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install -j$(nproc) mbstring opcache bcmath \
    && apt-mark manual unixodbc msodbcsql18 \
    && apt-get purge -y g++ make autoconf pkg-config unixodbc-dev $PHPIZE_DEPS \
    && apt-get autoremove -y \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/pear

# OPcache & PHP-FPM config
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

# Copy vendor từ stage 1
COPY --from=composer-stage /app/vendor ./vendor

# Copy toàn bộ source (đảm bảo có .dockerignore loại trừ vendor/, .env, .git, node_modules...)
COPY . .

# Khôi phục script autoload (package:discover) sau khi đã có đủ source code
RUN php artisan package:discover --ansi

# Tạo SQLite database file cho api_clients + thư mục storage
RUN mkdir -p database \
    && touch database/database.sqlite \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data database storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache database

COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]