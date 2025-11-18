FROM docker.io/serversideup/php:8.3-fpm-nginx-debian

WORKDIR /var/www/html

USER root

# Install required packages & dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install gd \
    && rm -rf /var/lib/apt/lists/*

# Buat folder Laravel cache & storage
RUN mkdir -p /var/www/html/bootstrap/cache \
    /var/www/html/storage/logs \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/app/public \
    && chmod -R 777 /var/www/html/bootstrap/cache /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache /var/www/html/storage

USER www-data

# Copy source code
COPY --chown=www-data:www-data . .

# Copy entrypoint scripts
COPY --chmod=755 ./entrypoint.d/ /etc/entrypoint.d

# Install composer dependencies
RUN cp .env.example .env \
    && composer install --no-dev --optimize-autoloader \
    && rm -rf .env bootstrap/cache/*.php
