FROM php:8.2.4RC1-fpm

RUN apt-get update && apt-get install -y wget git unzip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# Starter
COPY start.sh /
RUN chmod +x $app/start.sh
CMD ["/start.sh", "php-fpm","-F"]

WORKDIR /var/www