FROM composer:2 AS composer

FROM php:8.3-fpm-alpine

# Extensions système nécessaires à Laravel + MySQL
RUN apk add --no-cache \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/authentication-api

# Use the already installed dependencies because the locked development
# package is not currently downloadable from its GitHub commit.
COPY composer.json composer.lock ./
COPY vendor ./vendor

# Le code est monté en volume par docker-compose (voir docker-compose.yml).
# Les dépendances restent dans un volume Docker dédié défini dans
# docker-compose.yml.

EXPOSE 9000
CMD ["php-fpm"]