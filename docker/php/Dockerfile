FROM php:8.3-fpm-alpine

RUN apk add --no-cache curl git unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY composer.json .

RUN composer dump-autoload -o

COPY . .