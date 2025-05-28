# docker/php/Dockerfile
FROM php:8.2-fpm-alpine

WORKDIR /var/www/symfony

# Instalace systémových závislostí pro PHP rozšíření a Composer
RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    sqlite-dev \
    libxml2-dev \
    oniguruma-dev \
    git \
    unzip

# Instalace základních PHP rozšíření potřebných pro Symfony a SQLite
RUN docker-php-ext-install -j$(nproc) \
    intl \
    pdo \
    pdo_sqlite \
    zip \
    opcache \
    xml \
    mbstring

# Instalace Composeru globálně
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Vytvoření skupiny a uživatele www-data, pokud neexistují
# Standardní UID/GID pro www-data je často 33 nebo 82. Alpine fpm image může používat 82.
# Použijeme -o pro povolení duplicitního UID, pokud by již existoval s jiným jménem.
# Nebo jednoduše zkontrolujeme existenci.
RUN if ! getent group www-data > /dev/null; then \
        addgroup -g 82 -S www-data; \
    fi && \
    if ! getent passwd www-data > /dev/null; then \
        adduser -u 82 -D -S -G www-data www-data -s /sbin/nologin; \
    fi

# Příkaz php-fpm se spustí automaticky z base image