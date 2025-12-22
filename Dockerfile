FROM php:8.2-apache

# --------------------------
# Base image
# --------------------------
FROM php:8.2-apache

# --------------------------
# Installer extensions PHP
# --------------------------
# Install required packages and PHP extensions
RUN set -eux \
	&& apt-get update \
	&& apt-get install -y --no-install-recommends curl ca-certificates \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install mysqli

# --------------------------
# Corriger conflit MPM et activer rewrite
# --------------------------
# Désactive les MPM incompatibles
RUN a2dismod mpm_event mpm_worker || true
# Active MPM prefork
RUN a2enmod mpm_prefork
# Active mod_rewrite
RUN a2enmod rewrite

# --------------------------
# Copier le projet dans Apache
# --------------------------
# Copy project files as www-data to avoid extra chown cost
COPY --chown=www-data:www-data . /var/www/html/

# Set secure permissions: directories 755, files 644
RUN find /var/www/html -type d -exec chmod 755 {} \; \
	&& find /var/www/html -type f -exec chmod 644 {} \; \
	&& chown -R www-data:www-data /var/www/html

# --------------------------
# Expose le port Apache
# --------------------------
EXPOSE 80

# --------------------------
# Healthcheck to detect crashed Apache process
# --------------------------
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
	CMD curl -f http://localhost/ || exit 1

# --------------------------
# Optionnel : démarrage Apache en mode foreground
# --------------------------
CMD ["apache2-foreground"]
