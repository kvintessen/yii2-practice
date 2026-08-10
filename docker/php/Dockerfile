FROM composer:2.8 AS composer

FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    zip \
    $PHPIZE_DEPS \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
    && apk del $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

RUN mkdir -p /var/www/html \
    && chown -R www-data:www-data /var/www/html

FROM base AS dev

COPY --from=composer /usr/bin/composer /usr/bin/composer

CMD ["php-fpm", "-F"]

FROM base AS prod

COPY --from=composer /usr/bin/composer /usr/bin/composer

CMD ["php-fpm", "-F"]
