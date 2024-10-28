FROM php:8.3.8-alpine3.20

ARG APP_ENV

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl https://getcomposer.org/composer-2.phar --output /usr/bin/composer && chmod +x /usr/bin/composer

RUN apk update && apk add --no-cache unzip supervisor

RUN apk add --no-cache pcre-dev $PHPIZE_DEPS && \
    pecl update-channels && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    docker-php-ext-configure pcntl --enable-pcntl && \
    docker-php-ext-install pcntl && \
    apk del $PHPIZE_DEPS

WORKDIR /app

COPY app /app

RUN composer update -d /app && composer install --no-dev -d /app && php /app/bin/console cache:warmup && chown -R www-data:www-data /app

RUN php bin/console cache:clear

RUN addgroup -S appgroup && adduser -S userevent -G appgroup
USER userevent