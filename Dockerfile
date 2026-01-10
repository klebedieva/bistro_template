FROM php:8.3-apache

# Basic dependencies
RUN apt-get update \
    && apt-get install -y unzip git libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# 🔥 STRICT: remove all MPM except prefork (must be before any a2enmod)
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true; \
    rm -f /etc/apache2/mods-enabled/mpm_event.conf \
         /etc/apache2/mods-enabled/mpm_event.load \
         /etc/apache2/mods-enabled/mpm_worker.conf \
         /etc/apache2/mods-enabled/mpm_worker.load 2>/dev/null || true; \
    a2enmod mpm_prefork

# Enable rewrite
RUN a2enmod rewrite

# Symfony: DocumentRoot = /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
