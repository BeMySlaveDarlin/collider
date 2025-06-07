FROM ghcr.io/roadrunner-server/roadrunner:2025.1.1 AS roadrunner
FROM php:8.3-cli AS php_base

ARG APP_PORT=9501
ARG TIMEZONE=UTC

RUN apt-get update && apt-get install -y \
    zlib1g-dev libpq-dev libzip-dev libbrotli-dev \
    libonig-dev libxslt1-dev libicu-dev libpq-dev \
    mc nano make git curl wget unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
RUN chmod +x /usr/local/bin/rr

RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > $PHP_INI_DIR/conf.d/tzone.ini

RUN docker-php-ext-install \
    pdo pdo_pgsql pgsql sockets \
    zip pcntl mbstring bcmath

RUN pecl update-channels \
    && pecl install swoole redis \
    && docker-php-ext-enable swoole redis

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/tmp
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV PATH=/usr/local/bin:/app/vendor/bin:/app/bin:${PATH}

COPY . /app

RUN chmod +x /app/bin/app.php \
    && chmod +x /app/bin/http.php

WORKDIR /app

EXPOSE ${APP_PORT}

FROM php_base AS php_dev

ENV APP_ENV=dev

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/php.dev.ini $PHP_INI_DIR/conf.d/99-app.ini

FROM php_base AS php_prod

ENV APP_ENV=prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/php.prod.ini $PHP_INI_DIR/conf.d/99-app.ini

FROM php_base AS composer_dev

CMD composer install \
    && composer dump-autoload --classmap-authoritative

FROM php_base AS composer_prod

CMD composer install --no-dev --optimize-autoloader \
    && composer dump-autoload --classmap-authoritative --no-dev
