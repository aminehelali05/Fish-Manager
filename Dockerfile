FROM php:8.2-apache

# --------------------------
# Base image
# --------------------------
FROM php:8.2-apache

# --------------------------
# Installer extensions PHP
# --------------------------
RUN docker-php-ext-install mysqli

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
COPY . /var/www/html/

# Définir les permissions correctes
RUN chown -R www-data:www-data /var/www/html

# --------------------------
# Expose le port Apache
# --------------------------
EXPOSE 80

# --------------------------
# Optionnel : démarrage Apache en mode foreground
# --------------------------
CMD ["apache2-foreground"]
