FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y unzip git libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# mod_rewrite для Symfony
RUN a2enmod rewrite

# ✅ Фикс: только один MPM
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork

# ✅ Symfony: корень сайта = /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
