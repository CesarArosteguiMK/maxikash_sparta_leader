FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# DocumentRoot -> /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Copiar proyecto
COPY . /var/www/html

# permisos
RUN chown -R www-data:www-data /var/www/html

# Cloud Run usa 8080
EXPOSE 8080

RUN sed -i 's/80/8080/g' \
    /etc/apache2/ports.conf \
    /etc/apache2/sites-available/000-default.conf

# DEBUG TEMPORAL
RUN ls -R /var/www/html/backend
