FROM composer:2.8.9 AS composer
FROM php:8.4-cli

LABEL maintainer="Rayan Levert <rayanlevert@msn.com>"

# Installing needed packages
RUN apt-get update -y && \
    apt-get install -y \
    git \
    zip

# Enabling xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Creates /app directory
RUN mkdir /app

# Volumes
VOLUME ["/app"]

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

CMD ["php"]