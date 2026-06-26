FROM php:8.2-apache

# Solucionar repositorios HTTP -> HTTPS
RUN sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources && \
    sed -i 's|http://security.debian.org|https://security.debian.org|g' /etc/apt/sources.list.d/debian.sources

# Instalar extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    unzip zip git libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar proyecto
COPY . /var/www/html/

# Escribir VirtualHost completamente limpio en puerto 8080
RUN printf '<VirtualHost *:8080>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html\n\
    \n\
    <Directory />\n\
        Options FollowSymLinks\n\
        AllowOverride None\n\
        Require all denied\n\
    </Directory>\n\
    \n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks MultiViews\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks MultiViews\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n' > /etc/apache2/sites-available/000-default.conf

# Cambiar puerto de escucha a 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# Permisos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

WORKDIR /var/www/html

EXPOSE 8080