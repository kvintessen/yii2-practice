FROM composer:2.8 AS composer

FROM php:8.4-fpm-alpine AS base

# Runtime shared libs stay installed; -dev headers + compilers are removed
# again once the extensions are built, so they never reach dev/build/prod.
RUN apk add --no-cache \
        icu-libs \
        libpq \
        libzip \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

RUN chown www-data:www-data /var/www/html

EXPOSE 9000

# ---------------------------------------------------------------------------
# dev: app source is bind-mounted by docker-compose.yml and `composer
# install` is run inside the running container (see CLAUDE.md) — this stage
# only adds the tooling that workflow needs on top of `base`.
# ---------------------------------------------------------------------------
FROM base AS dev

RUN apk add --no-cache bash curl git \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD pgrep -f "php-fpm: master process" || exit 1

CMD ["php-fpm", "-F"]

# ---------------------------------------------------------------------------
# build: bakes vendor/ and the application into a self-contained tree. Only
# the result of this stage (not composer, not build tools) is copied into
# the prod runtime below.
# ---------------------------------------------------------------------------
FROM base AS build

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --no-autoloader \
        --prefer-dist

COPY . .

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && mkdir -p runtime web/assets \
    && chown -R www-data:www-data runtime web/assets

# ---------------------------------------------------------------------------
# prod: lean runtime image — no composer, no compilers, non-root.
# ---------------------------------------------------------------------------
FROM base AS prod

ENV YII_ENV=prod \
    YII_DEBUG=0

COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html

USER www-data

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD pgrep -f "php-fpm: master process" || exit 1

CMD ["php-fpm", "-F"]
