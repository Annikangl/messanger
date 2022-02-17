FROM php:8.0.2-fpm

ENV TZ=Europe/Moscow

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get --allow-releaseinfo-change-suite update \
&& docker-php-ext-install pdo pdo_mysql
