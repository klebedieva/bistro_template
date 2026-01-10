FROM php:8.3-apache

# Basic dependencies
RUN apt-get update \
    && apt-get install -y unzip git libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

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

# Copy and setup entrypoint script for MPM fix at runtime
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
