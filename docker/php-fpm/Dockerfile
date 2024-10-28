FROM php:8.3.8-fpm-alpine3.20

ARG APP_ENV

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl https://getcomposer.org/composer-2.phar --output /usr/bin/composer && chmod +x /usr/bin/composer

RUN apk update && apk add --no-cache unzip

RUN docker-php-ext-install opcache

RUN apk add --no-cache pcre-dev $PHPIZE_DEPS && \
    pecl update-channels && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    docker-php-ext-configure pcntl --enable-pcntl && \
    docker-php-ext-install pcntl && \
    apk del $PHPIZE_DEPS

COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY docker/php-fpm/php.ini /usr/local/etc/php/

COPY docker/php-fpm/conf.d /usr/local/etc/php/

WORKDIR /app

COPY app /app

RUN composer update -d /app && composer install -d /app && php /app/bin/console cache:warmup && chown -R www-data:www-data /app

RUN php bin/console cache:clear

CMD ["php-fpm"]
