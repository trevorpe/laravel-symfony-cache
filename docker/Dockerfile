FROM php:8.2-alpine

RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS} \
      && pecl install redis \
      && docker-php-ext-enable redis \
      && apk del pcre-dev ${PHPIZE_DEPS} \
      && rm -rf /tmp/pear
