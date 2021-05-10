FROM php:8.0-fpm-alpine

WORKDIR /app

RUN apk --update upgrade \
    && apk add --no-cache autoconf automake make gcc g++ icu-dev \
    && apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS imagemagick-dev libtool \
    && export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
    && docker-php-ext-install -j $(nproc) pdo_mysql \
    && apk del .phpize-deps

COPY etc/php/ /usr/local/etc/php/

COPY composer.json .
COPY composer.lock .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD composer install --no-scripts --no-autoloader \
    && composer dump-autoload \
    && docker-php-entrypoint php-fpm