# docker/php/Dockerfile
FROM php:8.2-fpm-alpine


ARG UID=82
ARG GID=82


RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    sqlite-dev \
    libxml2-dev \
    oniguruma-dev \
    git \
    unzip \
    shadow \
    su-exec

RUN \
    if ! getent group www-data > /dev/null; then \
        echo "Group www-data not found, creating with GID $GID..."; \
        addgroup -g $GID -S www-data; \
    elif [ "$(getent group www-data | cut -d: -f3)" != "$GID" ]; then \
        echo "Group www-data exists, changing GID to $GID..."; \

        delgroup www-data && addgroup -g $GID -S www-data || groupmod -g $GID www-data ; \
    fi && \
    \
    if ! getent passwd www-data > /dev/null; then \
        echo "User www-data not found, creating with UID $UID and GID $GID..."; \
        adduser -u $UID -D -S -G www-data www-data -s /sbin/nologin; \
    elif [ "$(getent passwd www-data | cut -d: -f3)" != "$UID" ] || [ "$(getent passwd www-data | cut -d: -f4)" != "$GID" ]; then \
        echo "User www-data exists, changing UID to $UID and GID to $GID..."; \
        usermod -u $UID -g $GID www-data; \
    fi && \
    echo "www-data UID is now $(id -u www-data) and GID is $(id -g www-data)"

RUN docker-php-ext-install -j$(nproc) \
    intl \
    pdo \
    pdo_sqlite \
    zip \
    opcache \
    xml \
    mbstring

WORKDIR /var/www/symfony


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data var && \
    chmod -R 775 var 