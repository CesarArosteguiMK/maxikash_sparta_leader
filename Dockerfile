FROM php:8.2-apache

# Habilitar mod_rewrite (Laravel / rutas amigables)
RUN a2enmod rewrite

# Instalar extensiones comunes
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Cambiar DocumentRoot a /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Copiar proyecto
COPY . /var/www/html

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Permisos (MUY importante)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Puerto que usa Cloud Run
EXPOSE 8080

# Apache debe escuchar en 8080 (Cloud Run)
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
