FROM php:8.1-apache

RUN apt update -y && apt upgrade -y && \
    docker-php-ext-install mysqli  && docker-php-ext-enable mysqli

COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite