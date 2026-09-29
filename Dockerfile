FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    curl \
    make \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    intl \
    opcache \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json composer.lock* symfony.lock* ./
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist || true

COPY . .
RUN composer dump-autoload --optimize
