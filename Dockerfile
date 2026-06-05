# Imagen base: PHP 8.2 con Apache incluido
FROM php:8.2-apache

# Habilita mod_rewrite (necesario para tu .htaccess)
RUN a2enmod rewrite

# Copia todo el proyecto al directorio de trabajo de Apache
COPY . /var/www/html/

# Ajusta los permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configura Apache para que permita .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

# El puerto que expone el contenedor
EXPOSE 80