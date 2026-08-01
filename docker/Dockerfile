FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_sqlite
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY app/ /var/www/html/
RUN mkdir -p /var/www/data && chown -R www-data:www-data /var/www/data /var/www/html
EXPOSE 80
