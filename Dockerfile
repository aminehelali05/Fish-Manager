FROM php:8.2-apache

# Installer extensions PHP nécessaires
RUN docker-php-ext-install mysqli

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier le projet dans Apache
COPY . /var/www/html/

# Donner les permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
