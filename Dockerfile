FROM php:8.3-apache

# Basic dependencies
RUN apt-get update \
    && apt-get install -y unzip git libzip-dev libicu-dev libpq-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install zip pdo pdo_mysql pdo_pgsql intl gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Fix MPM: ensure only mpm_prefork is enabled (required for mod_php)
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork

# Enable rewrite
RUN a2enmod rewrite

# Symfony: DocumentRoot = /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Set ServerName to suppress warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Ensure Directory configuration allows .htaccess
RUN sed -ri -e 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf 2>/dev/null || true

# Copy and setup entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

# Copy composer files first for better Docker layer caching
COPY composer.json composer.lock symfony.lock ./

# Set production environment before installing dependencies
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Install PHP dependencies (production, no dev)
# Note: --no-scripts to avoid running scripts before all files are copied
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy the rest of the application
COPY . .

# Run composer scripts (asset compilation) and Symfony cache setup
RUN set -eux; \
    composer dump-autoload --optimize --classmap-authoritative --no-interaction || true; \
    php bin/console assets:install public --symlink --relative --env=prod --no-interaction || true; \
    php bin/console cache:clear --env=prod --no-debug --no-interaction || true; \
    php bin/console cache:warmup --env=prod --no-debug --no-interaction || true

# Create var directories with correct permissions
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/var

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
