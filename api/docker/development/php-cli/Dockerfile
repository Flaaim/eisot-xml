FROM php:8.4-cli-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions amqp

RUN apk add --no-cache bash coreutils git unzip postgresql-client
RUN install-php-extensions intl pdo_pgsql opcache zip apcu

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/bash -D app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY ./common/wait-for-it.sh /usr/local/bin/wait-for-it
COPY ./common/php/conf.d /usr/local/etc/php/conf.d
RUN chmod 555 /usr/local/bin/wait-for-it

WORKDIR /app

USER app

