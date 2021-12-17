FROM php:7.4.14-fpm

RUN apt-get --allow-releaseinfo-change-suite update \
&& docker-php-ext-install pdo pdo_mysql
