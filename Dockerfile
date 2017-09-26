FROM php:7.0-apache
MAINTAINER Bruno Perel

RUN a2enmod ssl
COPY letsencrypt-options-ssl.conf /etc/apache2/
COPY edgecreator.ducksmanager.net-fullchain.pem /etc/apache2/edgecreator.ducksmanager.net-fullchain.pem
COPY edgecreator.ducksmanager.net-privkey.pem /etc/apache2/edgecreator.ducksmanager.net-privkey.pem
COPY apache_vhost.conf /etc/apache2/sites-available/edgecreator.conf
RUN ln -s /etc/apache2/sites-available/edgecreator.conf /etc/apache2/sites-enabled/edgecreator.conf

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
      git wget unzip \
      libpng-dev libfreetype6-dev libmcrypt-dev libpng12-dev libjpeg-dev libpng-dev \
      npm #For Bower

RUN docker-php-ext-configure gd \
  --enable-gd-native-ttf \
  --with-freetype-dir=/usr/include/freetype2 \
  --with-png-dir=/usr/include \
  --with-jpeg-dir=/usr/include

RUN docker-php-ext-install gd opcache

RUN ln -s /usr/bin/nodejs /usr/bin/node && \
    npm install --global bower

RUN mkdir -p /var/www/edges && \
    chown -R www-data:www-data /var/www/edges && \
    chmod a+w -R /var/www/edges