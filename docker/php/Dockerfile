# =========================================================
# STAGE: base
# Image dasar PHP-FPM dengan ekstensi yang dibutuhkan Laravel
# =========================================================
FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    bash git curl \
    libpng-dev libzip-dev zip unzip \
    oniguruma-dev freetype-dev libjpeg-turbo-dev icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# =========================================================
# STAGE: dev
# Dipakai untuk local development. Kode di-mount lewat volume
# (lihat docker-compose.yml), jadi stage ini cukup base image
# tanpa tools tambahan.
# =========================================================
FROM base AS dev

CMD ["php-fpm"]

# =========================================================
# STAGE: assets
# Build frontend (Vite + FlyonUI) pakai Bun. Hasilnya folder
# public/build yang nanti dipakai stage production.
# =========================================================
FROM oven/bun:1 AS assets
WORKDIR /app

COPY package.json bun.lockb* ./
RUN bun install --frozen-lockfile

COPY resources ./resources
COPY public ./public
COPY vite.config.* ./
COPY tailwind.config.* ./

RUN bun run build

# =========================================================
# STAGE: vendor
# Install dependency PHP untuk production (tanpa dev deps),
# lalu copy seluruh source code aplikasi.
# =========================================================
FROM base AS vendor

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

# =========================================================
# STAGE: prod-app
# Image final untuk container PHP-FPM di production.
# Berisi source code + vendor + hasil build asset.
# =========================================================
FROM base AS prod-app

COPY docker/php/php.prod.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY --from=vendor /var/www/html /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data
CMD ["php-fpm"]

# =========================================================
# STAGE: prod-nginx
# Image Nginx yang HANYA berisi folder public/ (statis).
# Request .php diteruskan ke container prod-app lewat network.
# =========================================================
FROM nginx:1.27-alpine AS prod-nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=vendor /var/www/html/public /var/www/html/public
COPY --from=assets /app/public/build /var/www/html/public/build

EXPOSE 80