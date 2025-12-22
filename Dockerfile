FROM php:8.2-apache
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
RUN set -eux \
	&& a2dismod mpm_event mpm_worker || true \
	# remove any left-over symlinks that might still load another MPM
	&& rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
			  /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf || true \
	# ensure only prefork is enabled
	&& a2enmod mpm_prefork || true \
	# enable rewrite
	&& a2enmod rewrite

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
