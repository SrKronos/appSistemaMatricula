# Usa una imagen base oficial de PHP con Apache
FROM php:7.4-apache

# Instala extensiones necesarias para PHP y PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \                
    nano \                    
    iputils-ping \             
    net-tools \                
    && docker-php-ext-install pdo_pgsql

# Copia los archivos de la aplicación al contenedor
COPY . /var/www/html/

# Exponer el puerto 80 para el servidor web
EXPOSE 80
