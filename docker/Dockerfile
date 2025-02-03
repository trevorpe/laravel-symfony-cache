FROM php:8.2-alpine

RUN apk --no-cache add pcre-dev linux-headers ${PHPIZE_DEPS}

RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install xdebug-3.4.1 && docker-php-ext-enable xdebug

RUN apk del pcre-dev linux-headers ${PHPIZE_DEPS} && rm -rf /tmp/pear
