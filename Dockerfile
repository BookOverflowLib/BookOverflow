FROM php:8.1-apache

RUN apt update -y && apt upgrade -y && \
docker-php-ext-install mysqli  && docker-php-ext-enable mysqli