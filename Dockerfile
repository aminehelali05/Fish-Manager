FROM php:8.2-apache

# Installer extensions PHP nécessaires
RUN docker-php-ext-install mysqli

# Ensure only one MPM is loaded (prefer prefork) and enable rewrite
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork
RUN a2enmod rewrite

# Copier le projet dans Apache
COPY . /var/www/html/

# Donner les permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
