FROM php:8.3-apache

# Basic dependencies
RUN apt-get update \
    && apt-get install -y unzip git libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 🔥 STRICT: remove all MPM except prefork (must be before any a2enmod)
RUN set -eux; \
    # First, disable all MPM modules
    for mpm in mpm_event mpm_worker; do \
        a2dismod $mpm 2>/dev/null || true; \
        rm -f /etc/apache2/mods-enabled/${mpm}.conf 2>/dev/null || true; \
        rm -f /etc/apache2/mods-enabled/${mpm}.load 2>/dev/null || true; \
    done; \
    # Disable prefork if enabled, then re-enable it
    a2dismod mpm_prefork 2>/dev/null || true; \
    rm -f /etc/apache2/mods-enabled/mpm_prefork.* 2>/dev/null || true; \
    # Now enable only prefork
    a2enmod mpm_prefork; \
    # Double-check: remove any remaining MPM symlinks except prefork
    find /etc/apache2/mods-enabled -name 'mpm_*.conf' ! -name 'mpm_prefork.conf' -delete 2>/dev/null || true; \
    find /etc/apache2/mods-enabled -name 'mpm_*.load' ! -name 'mpm_prefork.load' -delete 2>/dev/null || true

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

# Copy and setup entrypoint script for MPM fix at runtime
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
    php bin/console cache:clear --env=prod --no-debug --no-interaction || true; \
    php bin/console cache:warmup --env=prod --no-debug --no-interaction || true

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/var

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
