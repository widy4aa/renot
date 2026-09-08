# ─────────────────────────────────────────────────────────────
# Stage 1 — Node: build frontend assets (Vue + Tailwind)
# ─────────────────────────────────────────────────────────────
FROM node:22-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY resources/ resources/
COPY vite.config.js ./
COPY public/ public/

RUN npm run build

# ─────────────────────────────────────────────────────────────
# Stage 2 — PHP: app image (php-fpm + nginx + extensions)
# ─────────────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine AS app

# Sistem dependencies
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    supervisor

# PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        opcache \
        pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files dulu (cache layer)
COPY composer.json composer.lock ./

# Install PHP dependencies (tanpa dev, tanpa scripts)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

# Copy seluruh source code
COPY . .

# Copy hasil build frontend dari stage 1
COPY --from=node-builder /app/public/build ./public/build

# Copy konfigurasi Docker
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/renot.ini
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh

# Permissions
RUN chmod +x /start.sh \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose port app
EXPOSE 8000

ENTRYPOINT ["/start.sh"]
