# ────────────────────────────────────────────────────────────
# Stage 1: Composer install
# ────────────────────────────────────────────────────────────
FROM php:8.2-cli-bookworm AS composer-stage

# Cài ODBC + SQL Server driver (pdo_sqlsrv cần msodbcsql)
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        gnupg \
        unzip \
        git \
        g++ \
        make \
        unixodbc-dev \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
        msodbcsql18 \
    && pecl install sqlsrv-5.11.1 pdo_sqlsrv-5.11.1 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
# Chạy composer install mà không cần gán scripts lúc này
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# ────────────────────────────────────────────────────────────
# Stage 2: PHP-FPM Production
# ────────────────────────────────────────────────────────────
FROM php:8.2-fpm-bookworm AS production

LABEL maintainer="HUCE-ISRS Team"

# FIX LỖI: Thêm libonig-dev cho mbstring và giữ lại các package biên dịch cần thiết
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        gnupg \
        unixodbc \
        unixodbc-dev \
        g++ \
        make \
        libonig-dev \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
        msodbcsql18 \
    && pecl install sqlsrv-5.11.1 pdo_sqlsrv-5.11.1 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install -j$(nproc) pdo mbstring opcache bcmath \
    # Clean up triệt để các package phục vụ compile để giảm dung lượng image
    && apt-get purge -y g++ make libonig-dev unixodbc-dev \
    && apt-get autoremove -y \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# OPcache config
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

# Copy vendor từ stage 1 sang
COPY --from=composer-stage /app/vendor ./vendor

# Copy toàn bộ mã nguồn vào trước khi chạy artisan command
COPY . .

# Khôi phục script tự động nhận diện gói của Laravel
RUN php artisan package:discover --ansi

# Tạo cấu trúc thư mục, SQLite và phân quyền chuẩn xác cho www-data
RUN mkdir -p database \
    && touch database/database.sqlite \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database

COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]