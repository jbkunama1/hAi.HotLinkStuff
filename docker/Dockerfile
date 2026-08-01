FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-configure pdo_sqlite \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Apache vorbereiten
RUN a2enmod rewrite \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && echo 'DirectoryIndex index.html index.php' >> /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY app/ /var/www/html/app/
COPY app/index.php /var/www/html/index.php

RUN mkdir -p /var/www/data \
    && chown -R www-data:www-data /var/www/data /var/www/html

EXPOSE 80
