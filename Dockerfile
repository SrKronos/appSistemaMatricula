# Usa una imagen base oficial de PHP con Apache
FROM php:7.4-apache

# Instala extensiones necesarias para PHP y PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \                
    nano \                     
    iputils-ping \            
    net-tools \                
    unzip \                    
    && docker-php-ext-install pdo_pgsql

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia los archivos de la aplicación al contenedor
COPY . /var/www/html/

# Ejecuta Composer para instalar dependencias
WORKDIR /var/www/html/
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Exponer el puerto 80 para el servidor web
EXPOSE 80
