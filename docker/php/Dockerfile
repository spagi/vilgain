# docker/php/Dockerfile
FROM php:8.2-fpm-alpine

WORKDIR /var/www/symfony

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    sqlite-dev \
    libxml2-dev \
    oniguruma-dev \
    git \
    unzip


RUN docker-php-ext-install -j$(nproc) \
    intl \
    pdo \
    pdo_sqlite \
    zip \
    opcache \
    xml \
    mbstring


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


RUN if ! getent group www-data > /dev/null; then \
        addgroup -g 82 -S www-data; \
    fi && \
    if ! getent passwd www-data > /dev/null; then \
        adduser -u 82 -D -S -G www-data www-data -s /sbin/nologin; \
    fi