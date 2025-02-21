# syntax=docker/dockerfile:1.6

FROM dunglas/frankenphp:1-php8.4-bookworm as php-base
WORKDIR /build
RUN apt-get update && apt-get upgrade -y \
    # lib* packages are required for PHP extensions (dev packages are installed only for build)
    # These deps are required only for building PHP extensions and can be safely removed afterwards
    && export PHP_DEV_DEPS='libfreetype6-dev libicu-dev libjpeg-dev libmagickwand-dev libonig-dev libpng-dev librdkafka-dev libtidy-dev libxslt-dev libzip-dev libzstd-dev libgmp-dev libsodium-dev liblz4-dev' \
    && apt-get install -y --no-install-recommends \
        acl \
        curl \
        supervisor \
        procps \
        mc \
        kafkacat \
        dnsutils \
        gnupg \
        wget \
        libjpeg62-turbo \
        libgomp1 \
        libmagickwand-6.q16-6 \
        libpng16-16 \
        librdkafka1 \
        openssh-client \
        unzip \
        zip \
        libzip4 \
        zlib1g-dev \
        nano \
        libpcre3-dev \
        libpq-dev \
        $PHP_DEV_DEPS \
    && pecl install \
        redis \
        igbinary \
        msgpack \
        rdkafka \
    && docker-php-ext-configure exif \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j4 \
        gd \
        bcmath \
        gmp \
        intl \
        opcache \
        pcntl \
        sockets \
        zip \
        pdo_pgsql \
        mbstring \
        sodium \
        exif \
        bz2 \
    && docker-php-ext-enable \
        msgpack \
        igbinary \
        rdkafka \
        redis \
    && apt purge -y $PHP_DEV_DEPS $PHPIZE_DEPS \
    && apt autoremove -y --purge \
    && apt clean all \
    && rm -rf /var/lib/apt/lists/* \
    && rm -Rf /tmp/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --link docker/api/conf.d/php.ini $PHP_INI_DIR/conf.d/
COPY --link --chmod=755 ./docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link ./Caddyfile /etc/caddy/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

# APP BUILD
FROM php-base as app-build
COPY . ./

COPY docker/api/conf.d/php.ini $PHP_INI_DIR/conf.d/
RUN chown -R www-data:www-data "$PHP_INI_DIR/conf.d"

ENV COMPOSER_HOME /.composer
RUN --mount=type=secret,id=composer COMPOSER_ALLOW_SUPERUSER=1 composer install --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader

# APP DEV
FROM php-base as app-dev
WORKDIR /app
RUN chown -R www-data:www-data /app
ENV CHROOT_WWW_DIR=/app

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY docker/api/conf.d/php.dev.ini $PHP_INI_DIR/conf.d/
RUN chown -R www-data:www-data "$PHP_INI_DIR/conf.d"

RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp
RUN chown -R www-data:www-data /data/caddy && chown -R www-data:www-data /config/caddy

USER www-data

COPY --chown=www-data:www-data . .
COPY --from=app-build --chown=www-data:www-data /build/vendor/ ./vendor/

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]

# APP PROD
FROM php-base as app-prod
WORKDIR /app
ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="import worker.Caddyfile"

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link docker/api/conf.d/php.prod.ini $PHP_INI_DIR/conf.d/
COPY --link docker/api/worker.Caddyfile /etc/caddy/worker.Caddyfile

COPY . ./

RUN set -eux; \
	COMPOSER_ALLOW_SUPERUSER=1 composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress \

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]