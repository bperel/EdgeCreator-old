FROM php:7.3-apache
MAINTAINER Bruno Perel

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
      git wget unzip curl gnupg \
      libpng-dev libfreetype6-dev libmcrypt-dev libjpeg-dev libpng-dev

RUN curl -sL https://deb.nodesource.com/setup_13.x | bash - && apt-get install -y nodejs

RUN docker-php-ext-configure gd \
  --with-freetype-dir=/usr/include/freetype2 \
  --with-png-dir=/usr/include \
  --with-jpeg-dir=/usr/include

RUN docker-php-ext-install gd opcache

RUN npm install --global bower

RUN mkdir -p /var/www/edges && \
    chown -R www-data:www-data /var/www/edges && \
    chmod a+w -R /var/www/edges
