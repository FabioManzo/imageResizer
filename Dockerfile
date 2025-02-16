FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libmagickwand-dev \
    && docker-php-ext-install xml gd zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer


# PHP-FPM port
EXPOSE 9000
