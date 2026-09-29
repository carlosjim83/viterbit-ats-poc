FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    bash \
    curl \
    linux-headers \
    $PHPIZE_DEPS

RUN docker-php-ext-install pdo_pgsql zip

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* symfony.lock* ./
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist || true

COPY . .
RUN composer dump-autoload --optimize
