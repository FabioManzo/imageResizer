FROM php:8.2-fpm

RUN apt-get update && \
    apt-get install -y libmagickwand-dev && \
    docker-php-ext-install xml gd && \
    pecl install imagick && \
    docker-php-ext-enable imagick

# PHP-FPM port
EXPOSE 9000
